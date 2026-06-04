<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientCaseMessage extends Model
{
    protected $fillable = [
        'patient_id',
        'user_id',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public static function markIncomingAsReadFor(Patient $patient, int $viewerUserId): void
    {
        if (! Schema::hasColumn('patient_case_messages', 'read_at')) {
            return;
        }

        $patient->caseMessages()
            ->where('user_id', '!=', $viewerUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasAttachment(): bool
    {
        return ! empty($this->attachment_path);
    }

    public function isImageAttachment(): bool
    {
        if (! $this->attachment_mime) {
            return false;
        }

        return Str::startsWith($this->attachment_mime, 'image/');
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path || ! Storage::disk('public')->exists($this->attachment_path)) {
            return null;
        }

        return asset('storage/'.$this->attachment_path);
    }

    /** Inline image preview (auth route — works without public storage symlink). */
    public function attachmentPreviewUrl(Patient $patient): ?string
    {
        if (! $this->isImageAttachment() || ! $this->attachment_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->attachment_path)) {
            return null;
        }

        return route('patients.messages.attachment', [$patient, $this]);
    }

    public function attachmentDownloadUrl(Patient $patient): string
    {
        return route('patients.messages.attachment', [
            $patient,
            $this,
            'download' => 1,
        ]);
    }

    public function attachmentExtension(): string
    {
        $name = $this->attachment_name ?? '';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return $ext !== '' ? $ext : 'file';
    }

    public function attachmentSizeLabel(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($this->attachment_path)) {
            return null;
        }

        return self::formatBytes((int) $disk->size($this->attachment_path));
    }

    public function attachmentIconKind(): string
    {
        $ext = $this->attachmentExtension();

        return match (true) {
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true) => 'image',
            in_array($ext, ['doc', 'docx', 'rtf'], true) => 'word',
            in_array($ext, ['xls', 'xlsx', 'csv'], true) => 'sheet',
            in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'], true) => 'archive',
            in_array($ext, ['stl', 'obj', 'ply'], true) => 'scan',
            default => 'file',
        };
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}
