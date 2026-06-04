<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Patient extends Model
{
    public const STATUS_ACTIVE = 'approved';

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'age',
        'gender',
        'phone',
        'email',
        'address',
        'country',
        'status',
        'case_workflow_stage',
        'manufactured_at',
        'manufactured_by',
        'case_type',
        'photo',
        'upper_jaw_scan',
        'upper_jaw_scan_name',
        'lower_jaw_scan',
        'lower_jaw_scan_name',
        'notes',
        'last_visit',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'last_visit' => 'date',
            'manufactured_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Patient $patient) {
            $patient->deleteScanFiles();
            $patient->deleteAllPhotos();
        });
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function manufacturedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manufactured_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PatientPhoto::class)->orderBy('sort_order');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function caseMessages(): HasMany
    {
        return $this->hasMany(PatientCaseMessage::class)->orderBy('created_at');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(PatientTreatmentPlan::class);
    }

    public function caseModifications(): HasMany
    {
        return $this->hasMany(PatientCaseModification::class)->orderByDesc('created_at');
    }

    public function caseRefinements(): HasMany
    {
        return $this->hasMany(PatientCaseRefinement::class)->orderByDesc('created_at');
    }

    public function currentRefinement(): ?PatientCaseRefinement
    {
        if (! Schema::hasTable('patient_case_refinements')) {
            return null;
        }

        return $this->caseRefinements()->where('is_current', true)->latest('id')->first();
    }

    public function hasActiveRefinement(): bool
    {
        return $this->currentRefinement() !== null;
    }

    public function activeRefinementId(): ?int
    {
        return $this->currentRefinement()?->id;
    }

    /** Treatment plans for the active case or refinement cycle. */
    public function treatmentPlansQuery()
    {
        $refinementId = $this->activeRefinementId();

        if ($refinementId) {
            return $this->treatmentPlans()->where('refinement_id', $refinementId);
        }

        return $this->treatmentPlans()->whereNull('refinement_id');
    }

    /** Original manufactured case plans (before any active refinement cycle). */
    public function originalCycleTreatmentPlansQuery()
    {
        return $this->treatmentPlans()->whereNull('refinement_id');
    }

    public function originalCycleFullTreatmentPlan(): ?PatientTreatmentPlan
    {
        return $this->originalCycleTreatmentPlansQuery()
            ->whereNull('stage_number')
            ->where('is_current', true)
            ->latest('id')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PatientTreatmentPlan>
     */
    public function originalCycleStageTreatmentPlans()
    {
        return $this->originalCycleTreatmentPlansQuery()
            ->whereNotNull('stage_number')
            ->where('is_current', true)
            ->orderBy('stage_number')
            ->get();
    }

    public function isManufactured(): bool
    {
        return $this->workflowStageKey() === 'manufactured';
    }

    /** Doctor approved all plans in the active scope; admin may mark as manufactured. */
    public function isReadyForManufacturedMark(): bool
    {
        if ($this->workflowStageKey() !== 'approved') {
            return false;
        }

        if ($this->hasActiveModificationForAny()) {
            return false;
        }

        if ($this->hasActiveRefinement()) {
            if ($this->isDividedStages()) {
                $stages = $this->currentStageTreatmentPlans();

                return $stages->isNotEmpty()
                    && $stages->every(fn (PatientTreatmentPlan $plan) => $plan->isApproved());
            }

            $plan = $this->currentFullTreatmentPlan();

            return $plan !== null && $plan->isApproved();
        }

        if ($this->isDividedStages()) {
            $stages = $this->originalCycleStageTreatmentPlans();

            return $stages->isNotEmpty()
                && $stages->every(fn (PatientTreatmentPlan $plan) => $plan->isApproved());
        }

        $plan = $this->originalCycleFullTreatmentPlan();

        return $plan !== null && $plan->isApproved();
    }

    public function canRequestRefinement(): bool
    {
        if (! Schema::hasTable('patient_case_refinements')) {
            return false;
        }

        if ($this->hasActiveRefinement()) {
            return false;
        }

        if ($this->hasActiveModificationForAny()) {
            return false;
        }

        return $this->isManufactured();
    }

    public function isDividedStages(): bool
    {
        return $this->case_type === 'divided_stages';
    }

    public function currentFullTreatmentPlan(): ?PatientTreatmentPlan
    {
        return $this->treatmentPlansQuery()
            ->whereNull('stage_number')
            ->where('is_current', true)
            ->latest('id')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PatientTreatmentPlan>
     */
    public function currentStageTreatmentPlans()
    {
        return $this->treatmentPlansQuery()
            ->whereNotNull('stage_number')
            ->where('is_current', true)
            ->orderBy('stage_number')
            ->get();
    }

    public function currentTreatmentPlanForStage(int $stageNumber): ?PatientTreatmentPlan
    {
        return $this->treatmentPlansQuery()
            ->where('stage_number', $stageNumber)
            ->where('is_current', true)
            ->latest('id')
            ->first();
    }

    public function canAdminUploadStageTreatmentPlan(int $stageNumber): bool
    {
        if ($this->hasActiveRefinement() || $this->hasActiveModificationFor($stageNumber)) {
            $current = $this->currentTreatmentPlanForStage($stageNumber);

            return $current === null || $current->isApproved();
        }

        $current = $this->currentTreatmentPlanForStage($stageNumber);

        return $current === null || $current->isRejected();
    }

    public function canAdminUploadFullTreatmentPlan(): bool
    {
        if ($this->hasActiveRefinement()) {
            $current = $this->currentFullTreatmentPlan();

            return $current === null || $current->isApproved();
        }

        if ($this->hasActiveModificationFor(null)) {
            $current = $this->currentFullTreatmentPlan();

            return $current === null || $current->isApproved();
        }

        $current = $this->currentFullTreatmentPlan();

        return $current === null || $current->isRejected();
    }

    public function currentModification(?int $stageNumber = null): ?PatientCaseModification
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return null;
        }

        $query = $this->caseModifications()->where('is_current', true);

        if ($stageNumber === null) {
            $query->whereNull('stage_number');
        } else {
            $query->where('stage_number', $stageNumber);
        }

        return $query->latest('id')->first();
    }

    public function hasActiveModificationFor(?int $stageNumber): bool
    {
        return $this->currentModification($stageNumber) !== null;
    }

    public function hasActiveModificationForAny(): bool
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return false;
        }

        return $this->caseModifications()->where('is_current', true)->exists();
    }

    /**
     * Doctor may request a modification whenever the current plan for this scope is approved
     * and no modification is already awaiting a revised plan from LineUp.
     */
    public function canRequestModification(?int $stageNumber = null): bool
    {
        if ($this->isManufactured()) {
            return false;
        }

        if ($this->hasActiveRefinement()) {
            return false;
        }

        if ($this->hasActiveModificationFor($stageNumber)) {
            return false;
        }

        if ($this->isDividedStages()) {
            if ($stageNumber === null) {
                return false;
            }

            $plan = $this->currentTreatmentPlanForStage($stageNumber);

            return $plan !== null
                && $plan->is_current
                && $plan->isApproved();
        }

        $plan = $this->currentFullTreatmentPlan();

        return $plan !== null
            && $plan->is_current
            && $plan->isApproved();
    }

    /**
     * Stage numbers where the doctor can start a new modification cycle right now.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function modificationEligibleStageNumbers()
    {
        return $this->currentStageTreatmentPlans()
            ->filter(fn (PatientTreatmentPlan $plan) => $this->canRequestModification($plan->stage_number))
            ->pluck('stage_number')
            ->map(fn ($n) => (int) $n)
            ->values();
    }

    public function canRequestModificationNow(): bool
    {
        if ($this->isDividedStages()) {
            return $this->modificationEligibleStageNumbers()->isNotEmpty();
        }

        return $this->canRequestModification(null);
    }

    public function hasModificationAwaitingPlan(?int $stageNumber = null): bool
    {
        return $this->hasActiveModificationFor($stageNumber);
    }

    /**
     * @return list<array{key: string, label: string, notes: ?string, files: list<array>}>
     */
    public function caseScanSetsForViewer(): array
    {
        $sets = [];

        $original = $this->caseScanFiles();
        if (count($original) > 0) {
            $sets[] = [
                'key' => 'original',
                'label' => 'Original case scans',
                'notes' => null,
                'files' => $original,
            ];
        }

        if (! Schema::hasTable('patient_case_modifications')) {
            return $sets;
        }

        foreach ($this->caseModifications()->orderBy('version')->get() as $mod) {
            $files = $mod->caseScanFiles();
            if (count($files) === 0) {
                continue;
            }

            $sets[] = [
                'key' => 'mod-'.$mod->id,
                'label' => $mod->scopeLabel(),
                'notes' => $mod->notes,
                'files' => $files,
            ];
        }

        if (Schema::hasTable('patient_case_refinements')) {
            foreach ($this->caseRefinements()->orderBy('version')->get() as $ref) {
                $files = $ref->caseScanFiles();
                if (count($files) === 0) {
                    continue;
                }

                $sets[] = [
                    'key' => 'ref-'.$ref->id,
                    'label' => $ref->scopeLabel(),
                    'notes' => $ref->notes,
                    'files' => $files,
                ];
            }
        }

        return $sets;
    }

    /**
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleFullTreatmentPlans()
    {
        $plans = $this->treatmentPlansQuery()
            ->whereNull('stage_number')
            ->orderBy('version')
            ->get();

        $current = $plans->firstWhere('is_current', true);

        if ($current === null) {
            return $plans;
        }

        if ($current->isApproved() && ! $this->hasActiveModificationFor(null) && ! $this->hasActiveRefinement()) {
            return collect([$current]);
        }

        return $plans->filter(function (PatientTreatmentPlan $plan) use ($current) {
            return $plan->is_current
                || ($plan->version < $current->version && ($plan->isRejected() || $plan->isApproved()));
        })->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleTreatmentPlansForStage(int $stageNumber)
    {
        $plans = $this->treatmentPlansQuery()
            ->where('stage_number', $stageNumber)
            ->orderBy('version')
            ->get();

        $current = $plans->firstWhere('is_current', true);

        if ($current === null) {
            return $plans;
        }

        if ($current->isApproved() && ! $this->hasActiveModificationFor($stageNumber) && ! $this->hasActiveRefinement()) {
            return collect([$current]);
        }

        return $plans->filter(function (PatientTreatmentPlan $plan) use ($current) {
            return $plan->is_current
                || ($plan->version < $current->version && ($plan->isRejected() || $plan->isApproved()));
        })->values();
    }

    /**
     * Stage numbers that have at least one saved plan (for navigation).
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function treatmentPlanStageNumbers()
    {
        return $this->treatmentPlansQuery()
            ->whereNotNull('stage_number')
            ->distinct()
            ->orderBy('stage_number')
            ->pluck('stage_number')
            ->map(fn ($n) => (int) $n)
            ->values();
    }

    public function planReviewOverlay(): ?string
    {
        if ($this->isDividedStages()) {
            $stages = $this->currentStageTreatmentPlans();
            if ($stages->isEmpty()) {
                return null;
            }
            if ($stages->contains(fn (PatientTreatmentPlan $plan) => $plan->isRejected())) {
                return 'rejected';
            }
            if ($stages->every(fn (PatientTreatmentPlan $plan) => $plan->isApproved())) {
                return 'approved';
            }

            return 'pending';
        }

        $plan = $this->currentFullTreatmentPlan();
        if (! $plan) {
            return null;
        }

        return $plan->review_status;
    }

    public function workflowStageKey(): string
    {
        $stage = $this->attributes['case_workflow_stage'] ?? null;

        if (! empty($stage)) {
            return $stage;
        }

        return config('patient-case-workflow.status_fallback.'.$this->status)
            ?? config('patient-case-workflow.default_stage', 'created');
    }

    /** Maps internal workflow stage to a progress-bar step key. */
    public function progressBarStageKey(): string
    {
        $internal = $this->workflowStageKey();
        $overlay = $this->planReviewOverlay();

        if ($internal === 'waiting_plan' && in_array($overlay, ['pending', 'rejected'], true)) {
            return 'case_status';
        }

        if ($this->hasActiveRefinement()) {
            return 'refinement';
        }

        return match ($internal) {
            'manufactured' => 'approved',
            'modification' => 'waiting_plan',
            'created' => 'created',
            'approved' => 'approved',
            'waiting_plan' => 'waiting_plan',
            default => $internal,
        };
    }

    /**
     * @return array<int, array{key: string, label: string, state: string, index: int, variant?: string}>
     */
    public function workflowProgress(): array
    {
        $steps = config('patient-case-workflow.progress_steps', []);
        $progressKey = $this->progressBarStageKey();
        $internalKey = $this->workflowStageKey();
        $planOverlay = $this->planReviewOverlay();
        $inModification = $internalKey === 'modification' && $this->hasActiveModificationForAny();
        $currentIndex = 0;

        foreach ($steps as $i => $step) {
            if ($step['key'] === $progressKey) {
                $currentIndex = $i;
                break;
            }
        }

        return collect($steps)->map(function (array $step, int $index) use (
            $currentIndex,
            $internalKey,
            $planOverlay,
            $inModification
        ) {
            $key = $step['key'];
            $label = $step['label'];
            $state = $index < $currentIndex ? 'completed' : ($index === $currentIndex ? 'current' : 'upcoming');
            $variant = null;

            if ($key === 'waiting_plan') {
                if ($inModification && $index === $currentIndex) {
                    $label = 'Modification · Awaiting new plan';
                    $variant = 'modification';
                } elseif ($index === $currentIndex && $internalKey === 'waiting_plan' && $planOverlay === null) {
                    $label = 'Awaiting treatment plan';
                } elseif ($index < $currentIndex) {
                    $label = $inModification ? 'Modification requested' : 'Treatment plan uploaded';
                }
            }

            if ($key === 'case_status') {
                if ($planOverlay === 'pending' && $index === $currentIndex) {
                    $state = 'current';
                    $label = $inModification || $this->hasActiveModificationForAny()
                        ? 'Awaiting doctor approval · Mod'
                        : 'Awaiting doctor approval';
                    if ($this->hasActiveRefinement()) {
                        $label = 'Awaiting doctor approval · Refinement';
                        $variant = 'refinement-review';
                    }
                } elseif ($planOverlay === 'rejected' && $index === $currentIndex) {
                    $state = 'rejected';
                    $label = 'Plan rejected';
                } elseif ($index < $currentIndex) {
                    $state = 'completed';
                    $label = 'Doctor approved';
                }
            }

            if ($key === 'approved') {
                if ($this->isReadyForManufacturedMark() && $index === $currentIndex) {
                    $state = 'current';
                    $label = 'Ready to mark manufactured';
                } elseif ($internalKey === 'manufactured' && $index === $currentIndex) {
                    $state = 'current';
                    $label = 'Manufactured · Complete';
                } elseif ($internalKey === 'approved' && $index === $currentIndex) {
                    $state = 'current';
                    $label = 'Approved for manufacture';
                } elseif ($index < $currentIndex && ($this->manufactured_at || $internalKey === 'manufactured')) {
                    $state = 'completed';
                    $label = 'Manufactured';
                }
            }

            if ($key === 'refinement') {
                if ($index === $currentIndex && $this->hasActiveRefinement()) {
                    $state = 'current';
                    $variant = 'refinement';
                    $label = match (true) {
                        $internalKey === 'waiting_plan' && $planOverlay === null => 'Refinement · Awaiting plan',
                        default => 'Refinement in progress',
                    };
                } elseif ($index < $currentIndex) {
                    $state = 'completed';
                    $label = 'Refinement complete';
                }
            }

            $row = [
                'key' => $key,
                'label' => $label,
                'state' => $state,
                'index' => $index,
            ];

            if ($variant !== null) {
                $row['variant'] = $variant;
            }

            return $row;
        })->all();
    }

    /** Same label as the active step on the case workflow progress bar. */
    public function workflowStageLabel(): string
    {
        foreach ($this->workflowProgress() as $step) {
            if (in_array($step['state'], ['current', 'rejected'], true)) {
                return $step['label'];
            }
        }

        $key = $this->workflowStageKey();

        foreach (config('patient-case-workflow.progress_steps', []) as $step) {
            if ($step['key'] === $this->progressBarStageKey()) {
                return $step['label'];
            }
        }

        return ucfirst(str_replace('_', ' ', $this->progressBarStageKey()));
    }

    public function genderAgeLabel(): string
    {
        $gender = match ($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            default => '—',
        };

        $age = $this->age;
        if ($age === null && $this->date_of_birth) {
            $age = $this->date_of_birth->age;
        }

        return $gender.' / '.($age ?? 0).' Years';
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function caseTypeLabel(): string
    {
        return config('patient-case-types.'.$this->case_type, ucfirst(str_replace('_', ' ', $this->case_type ?? '')));
    }

    public function statusLabel(): string
    {
        $badge = config('patient-statuses.badges.'.$this->status);

        return $badge['label'] ?? ucfirst($this->status ?? '');
    }

    public function statusBadgeClass(): string
    {
        $badge = config('patient-statuses.badges.'.$this->status);

        return $badge['class'] ?? 'cases-badge-default';
    }

    public function workflowBadgeClass(): string
    {
        $key = $this->workflowStageKey();
        $suffix = config('patient-case-workflow.badge_classes.'.$key, 'workflow-default');

        return 'cases-workflow-badge cases-workflow-badge--'.$suffix;
    }

    public function caseTypeIcon(): string
    {
        return $this->case_type === 'divided_stages' ? 'zmdi-view-week' : 'zmdi-layers';
    }

    public function has3dScans(): bool
    {
        return (bool) ($this->upper_jaw_scan || $this->lower_jaw_scan);
    }

    public function caseAvatarUrl(): string
    {
        return asset('assets/images/case-dental-avatar.svg');
    }

    public function photoUrl(): string
    {
        $firstPhoto = $this->relationLoaded('photos')
            ? $this->photos->first()
            : $this->photos()->first();

        if ($firstPhoto) {
            return $firstPhoto->url();
        }

        if ($this->photo) {
            return asset('storage/'.$this->photo);
        }

        return $this->caseAvatarUrl();
    }

    public function upperJawScanUrl(): ?string
    {
        return $this->scanViewUrl('upper');
    }

    public function lowerJawScanUrl(): ?string
    {
        return $this->scanViewUrl('lower');
    }

    public function upperJawScanDownloadUrl(): ?string
    {
        return $this->scanDownloadUrl('upper');
    }

    public function lowerJawScanDownloadUrl(): ?string
    {
        return $this->scanDownloadUrl('lower');
    }

    public function scanViewUrl(string $scan): ?string
    {
        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';

        if (! $this->{$field} || ! Storage::disk('public')->exists($this->{$field})) {
            return null;
        }

        return route('patients.scans.download', [$this, $scan]);
    }

    public function scanDownloadUrl(string $scan): ?string
    {
        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';

        if (! $this->{$field} || ! Storage::disk('public')->exists($this->{$field})) {
            return null;
        }

        return route('patients.scans.download', [$this, $scan, 'download' => 1]);
    }

    /** @return list<array{id: string, label: string, name: string, ext: string, size: ?string, view_url: string, download_url: string}> */
    public function caseScanFiles(): array
    {
        $files = [];

        foreach ([
            'upper' => ['field' => 'upper_jaw_scan', 'label' => 'Upper 3D model'],
            'lower' => ['field' => 'lower_jaw_scan', 'label' => 'Lower 3D model'],
        ] as $id => $meta) {
            $field = $meta['field'];

            if (! $this->{$field}) {
                continue;
            }

            if (! Storage::disk('public')->exists($this->{$field})) {
                continue;
            }

            $viewUrl = $this->scanViewUrl($id);
            $downloadUrl = $this->scanDownloadUrl($id);

            if (! $viewUrl || ! $downloadUrl) {
                continue;
            }

            $files[] = [
                'id' => $id,
                'label' => $meta['label'],
                'name' => $this->scanDisplayName($this->{$field}, $field),
                'ext' => strtoupper($this->scanExtension($field)),
                'size' => $this->scanSizeLabel($field),
                'view_url' => $viewUrl,
                'download_url' => $downloadUrl,
            ];
        }

        return $files;
    }

    public function scanSizeLabel(string $field): ?string
    {
        $path = $this->{$field};

        return $this->scanSizeLabelForPath($path);
    }

    public function scanSizeLabelForPath(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return self::formatScanBytes((int) Storage::disk('public')->size($path));
    }

    private static function formatScanBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }

    public function scanNameField(string $field): string
    {
        return $field === 'upper_jaw_scan' ? 'upper_jaw_scan_name' : 'lower_jaw_scan_name';
    }

    public function scanExtension(string $field): string
    {
        $path = $this->{$field};
        $nameField = $this->scanNameField($field);
        $originalName = $this->{$nameField};

        if ($originalName) {
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (in_array($ext, ['stl', 'obj', 'ply'], true)) {
                return $ext;
            }
        }

        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['stl', 'obj', 'ply'], true)) {
                return $ext;
            }

            $fullPath = Storage::disk('public')->path($path);
            if (is_file($fullPath)) {
                $detected = $this->detectScanExtensionFromFile($fullPath);
                if ($detected) {
                    return $detected;
                }
            }
        }

        return 'stl';
    }

    public function scanDownloadFilename(string $field): string
    {
        $nameField = $this->scanNameField($field);
        $originalName = $this->{$nameField};

        if ($originalName) {
            $safe = preg_replace('/[^\w.\-]+/u', '_', basename($originalName));
            if ($safe !== '' && str_contains($safe, '.')) {
                return $safe;
            }
        }

        $label = $field === 'upper_jaw_scan' ? 'upper' : 'lower';
        $slug = str_replace(' ', '_', $this->patient_id ?? 'case');
        $ext = $this->scanExtension($field);

        return "{$slug}_{$label}.{$ext}";
    }

    public function scanDisplayName(?string $path, ?string $field = null): string
    {
        if ($field) {
            $nameField = $this->scanNameField($field);
            if ($this->{$nameField}) {
                return basename($this->{$nameField});
            }
        }

        if (! $path) {
            return '';
        }

        $basename = basename($path);
        if (preg_match('/\.(stl|obj|ply)$/i', $basename)) {
            return $basename;
        }

        if ($field) {
            return $this->scanDownloadFilename($field);
        }

        return $basename;
    }

    private function detectScanExtensionFromFile(string $fullPath): ?string
    {
        $handle = @fopen($fullPath, 'rb');
        if (! $handle) {
            return null;
        }

        $header = fread($handle, 512) ?: '';
        fclose($handle);

        if (str_starts_with($header, 'solid') || str_contains($header, 'facet normal')) {
            return 'stl';
        }

        if (preg_match('/^ply\s/i', $header) || str_contains($header, 'element vertex')) {
            return 'ply';
        }

        if (preg_match('/^v\s+[\d.\-+eE]/m', $header) || str_contains($header, 'mtllib')) {
            return 'obj';
        }

        $mime = @mime_content_type($fullPath) ?: '';
        if (str_contains($mime, 'stl')) {
            return 'stl';
        }

        return null;
    }

    public function deleteScanFiles(): void
    {
        foreach ([$this->upper_jaw_scan, $this->lower_jaw_scan] as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function deleteAllPhotos(): void
    {
        foreach ($this->photos as $photo) {
            if (Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }
        }
    }

    public static function generatePatientId(): string
    {
        $number = str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);

        return 'LA '.$number;
    }

    public static function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        $parts = explode(' ', $name, 2);

        return [
            'first_name' => $parts[0],
            'last_name' => $parts[1] ?? '',
        ];
    }

}
