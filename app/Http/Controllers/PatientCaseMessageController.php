<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCaseMessage;
use App\Models\User;
use App\Services\LineUpNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientCaseMessageController extends Controller
{
    /** 25 MB — all file types allowed */
    private const ATTACHMENT_MAX_KB = 25600;

    public function index(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('chat', $patient);

        if (! Schema::hasTable('patient_case_messages')) {
            return response()->json(['messages' => [], 'participants' => $this->participants($patient)]);
        }

        $query = $patient->caseMessages()->with(['user.doctor']);

        $afterId = (int) $request->input('after', 0);

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        } else {
            $query->limit(100);
        }

        PatientCaseMessage::markIncomingAsReadFor($patient, (int) auth()->id());

        $messages = $query->orderBy('created_at')->get();
        $latestSeenOwnId = $this->latestSeenOwnMessageId($patient);

        return response()->json([
            'messages' => $messages->map(fn (PatientCaseMessage $m) => $this->formatMessage($m, $patient, $latestSeenOwnId)),
            'seen_message_id' => $latestSeenOwnId,
            'participants' => $this->participants($patient),
        ]);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('chat', $patient);

        if (! Schema::hasTable('patient_case_messages')) {
            return response()->json(['error' => 'Chat is not available. Run database migrations.'], 503);
        }

        if (! $patient->doctor_id) {
            return response()->json(['error' => 'Assign a doctor to this case before using chat.'], 422);
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:'.self::ATTACHMENT_MAX_KB],
        ]);

        $body = trim($validated['body'] ?? '');

        if ($body === '' && ! $request->hasFile('attachment')) {
            return response()->json(['error' => 'Enter a message or attach a file.'], 422);
        }

        $data = [
            'user_id' => auth()->id(),
            'body' => $body !== '' ? $body : null,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $stored = $this->storeAttachment($patient, $file);
            $data = array_merge($data, $stored);
        }

        $message = $patient->caseMessages()->create($data);
        $message->load(['user.doctor']);

        $patient->load('doctor.user');
        $preview = $body !== '' ? $body : ($message->hasAttachment() ? 'Attachment: '.$message->attachment_name : null);
        app(LineUpNotifier::class)->caseMessage($patient, auth()->user(), $preview);

        return response()->json([
            'message' => $this->formatMessage($message, $patient, $this->latestSeenOwnMessageId($patient)),
        ], 201);
    }

    public function downloadAttachment(Request $request, Patient $patient, PatientCaseMessage $message): StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('chat', $patient);

        if ((int) $message->patient_id !== (int) $patient->id || ! $message->hasAttachment()) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($message->attachment_path)) {
            abort(404);
        }

        $filename = $message->attachment_name ?? 'attachment';
        $mime = $message->attachment_mime ?: 'application/octet-stream';

        if ($request->boolean('download') || ! $message->isImageAttachment()) {
            return $disk->download($message->attachment_path, $filename);
        }

        return response()->file($disk->path($message->attachment_path), [
            'Content-Type' => $mime,
        ]);
    }

    private function storeAttachment(Patient $patient, UploadedFile $file): array
    {
        $original = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'file';
        $filename = $base.'_'.time().'.'.$ext;
        $dir = "patients/{$patient->id}/chat";

        $path = $file->storeAs($dir, $filename, 'public');

        return [
            'attachment_path' => $path,
            'attachment_name' => $original,
            'attachment_mime' => $file->getMimeType() ?: 'application/octet-stream',
        ];
    }

    private function formatMessage(PatientCaseMessage $message, Patient $patient, ?int $latestSeenOwnId = null): array
    {
        $user = $message->user;
        $roleLabel = $this->authorRoleLabel($user, $patient);
        $isMine = (int) $message->user_id === (int) auth()->id();
        $latestSeenOwnId ??= $this->latestSeenOwnMessageId($patient);
        $showSeen = $isMine
            && $message->read_at
            && $latestSeenOwnId
            && (int) $message->id === $latestSeenOwnId;

        return [
            'id' => $message->id,
            'body' => $message->body ?? '',
            'author' => $user?->displayName() ?? 'System',
            'role' => $roleLabel,
            'role_short' => $user?->isAdmin() ? 'ADMINISTRATOR' : 'DOCTOR',
            'avatar_url' => $user?->photoUrl() ?? asset('assets/images/logo.svg'),
            'is_mine' => $isMine,
            'is_seen' => $isMine && $message->read_at !== null,
            'show_seen' => $showSeen,
            'time' => $message->created_at?->format('g:i A'),
            'time_full' => $message->created_at?->format('D, d M Y · g:i A'),
            'time_short' => $message->created_at?->diffForHumans(),
            'attachment' => $this->formatAttachment($message, $patient),
        ];
    }

    private function latestSeenOwnMessageId(Patient $patient): ?int
    {
        if (! Schema::hasColumn('patient_case_messages', 'read_at')) {
            return null;
        }

        $id = $patient->caseMessages()
            ->where('user_id', auth()->id())
            ->whereNotNull('read_at')
            ->max('id');

        return $id ? (int) $id : null;
    }

    private function formatAttachment(PatientCaseMessage $message, Patient $patient): ?array
    {
        if (! Schema::hasColumn('patient_case_messages', 'attachment_path') || ! $message->hasAttachment()) {
            return null;
        }

        return [
            'name' => $message->attachment_name,
            'mime' => $message->attachment_mime,
            'extension' => strtoupper($message->attachmentExtension()),
            'size' => $message->attachmentSizeLabel(),
            'icon' => $message->attachmentIconKind(),
            'is_image' => $message->isImageAttachment(),
            'url' => $message->attachmentPreviewUrl($patient),
            'download_url' => $message->attachmentDownloadUrl($patient),
        ];
    }

    private function authorRoleLabel(?User $user, Patient $patient): string
    {
        if (! $user) {
            return 'System';
        }

        if ($user->isAdmin()) {
            return 'Administrator';
        }

        if ($user->isDoctor() && $patient->doctor_id && $user->doctor?->id === $patient->doctor_id) {
            return 'Assigned Doctor';
        }

        return 'Doctor';
    }

    private function participants(Patient $patient): array
    {
        $doctor = $patient->relationLoaded('doctor') ? $patient->doctor : $patient->doctor()->with('user')->first();

        return [
            'doctor_name' => $doctor ? 'Dr. '.$doctor->fullName() : null,
            'doctor_user_id' => $doctor?->user_id,
            'admin_label' => 'System Administrator',
        ];
    }
}
