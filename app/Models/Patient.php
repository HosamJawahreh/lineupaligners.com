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

    public function originalPhotos(): HasMany
    {
        return $this->photos()->whereNull('modification_id')->whereNull('refinement_id');
    }

    public function hasAnyCasePhotos(): bool
    {
        return $this->photos()->exists();
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

    public function hasCompletedManufacturing(): bool
    {
        return $this->manufactured_at !== null
            || $this->workflowStageKey() === 'manufactured';
    }

    public function isManufactured(): bool
    {
        if ($this->hasActiveRefinement()) {
            return false;
        }

        return $this->hasCompletedManufacturing();
    }

    /** Doctor approved all plans in the active scope; admin may mark as manufactured. */
    public function isReadyForManufacturedMark(): bool
    {
        if ($this->hasActiveModificationForAny()) {
            return false;
        }

        if ($this->doctorReviewStageNumber() !== null) {
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

        if ($this->workflowStageKey() !== 'approved') {
            return false;
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

        return $this->hasCompletedManufacturing();
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

    /**
     * Divided stages: the stage currently in the pipeline (pending/rejected/mod, or next slot).
     */
    public function currentDividedStageNumber(): ?int
    {
        if (! $this->isDividedStages()) {
            return null;
        }

        $stageNumbers = $this->treatmentPlanStageNumbers();

        if ($stageNumbers->isEmpty()) {
            return 1;
        }

        foreach ($stageNumbers->sort()->values() as $num) {
            $plan = $this->currentTreatmentPlanForStage($num);

            if ($plan && ($plan->isPending() || $plan->isRejected())) {
                return $num;
            }

            if ($plan && $plan->isApproved() && $this->hasActiveModificationFor($num)) {
                return $num;
            }
        }

        $last = (int) $stageNumbers->max();
        $lastPlan = $this->currentTreatmentPlanForStage($last);

        if ($lastPlan && $lastPlan->isApproved() && ! $this->hasActiveModificationFor($last)) {
            return $last + 1;
        }

        return $last;
    }

    /** Stage with a pending plan awaiting doctor approve / reject (no active modification). */
    public function doctorReviewStageNumber(): ?int
    {
        if (! $this->isDividedStages()) {
            return null;
        }

        foreach ($this->treatmentPlanStageNumbers()->sort()->values() as $num) {
            $plan = $this->currentTreatmentPlanForStage($num);

            if ($plan && $plan->isPending() && ! $this->hasActiveModificationFor($num)) {
                return $num;
            }
        }

        return null;
    }

    public function canDoctorReviewStage(?int $stageNumber): bool
    {
        if ($stageNumber === null || ! $this->isDividedStages()) {
            return false;
        }

        return $this->doctorReviewStageNumber() === $stageNumber;
    }

    /**
     * Divided stages: one stage approved and admin may still upload the next stage.
     * Case must not enter manufacture-ready until more stages are added or admin marks manufactured.
     */
    public function isAwaitingNextDividedStageAfterSingleApproval(): bool
    {
        if (! $this->isDividedStages() || $this->hasActiveRefinement()) {
            return false;
        }

        if ($this->doctorReviewStageNumber() !== null) {
            return false;
        }

        $stages = $this->currentStageTreatmentPlans();

        if ($stages->count() !== 1) {
            return false;
        }

        $only = $stages->first();

        return $only !== null
            && $only->isApproved()
            && ! $this->hasActiveModificationFor($only->stage_number)
            && $this->canAdminAddNewDividedStage();
    }

    public function canAdminAddNewDividedStage(): bool
    {
        if (! $this->isDividedStages()) {
            return false;
        }

        $stageNumbers = $this->treatmentPlanStageNumbers();

        if ($stageNumbers->isEmpty()) {
            return true;
        }

        if ($this->doctorReviewStageNumber() !== null) {
            return false;
        }

        $last = (int) $stageNumbers->max();
        $lastPlan = $this->currentTreatmentPlanForStage($last);

        if (! $lastPlan || ! $lastPlan->isApproved()) {
            return false;
        }

        if ($this->hasActiveModificationFor($last)) {
            return false;
        }

        return $this->currentTreatmentPlanForStage($last + 1) === null;
    }

    public function canAdminAddNewDividedStageForStage(int $stageNumber): bool
    {
        if (! $this->isDividedStages()) {
            return false;
        }

        if ($this->currentTreatmentPlanForStage($stageNumber) !== null) {
            return false;
        }

        if ($stageNumber === 1) {
            return $this->treatmentPlanStageNumbers()->isEmpty();
        }

        $previous = $this->currentTreatmentPlanForStage($stageNumber - 1);

        if ($previous === null || ! $previous->isApproved()) {
            return false;
        }

        return ! $this->hasActiveModificationFor($stageNumber - 1);
    }

    public function canAdminUploadStageTreatmentPlan(int $stageNumber): bool
    {
        if ($this->hasActiveRefinement()) {
            $current = $this->currentTreatmentPlanForStage($stageNumber);

            return $current === null || $current->isApproved();
        }

        if ($this->hasActiveModificationFor($stageNumber)) {
            return true;
        }

        $current = $this->currentTreatmentPlanForStage($stageNumber);

        if ($current === null) {
            return $this->canAdminAddNewDividedStageForStage($stageNumber);
        }

        return $current->isRejected();
    }

    public function canAdminUploadFullTreatmentPlan(): bool
    {
        if ($this->hasActiveRefinement()) {
            $current = $this->currentFullTreatmentPlan();

            return $current === null || $current->isApproved();
        }

        if ($this->hasActiveModificationFor(null)) {
            return true;
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

    public function hasModificationHistory(): bool
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return false;
        }

        return $this->caseModifications()->exists();
    }

    public function hasRefinementHistory(): bool
    {
        if (! Schema::hasTable('patient_case_refinements')) {
            return false;
        }

        return $this->caseRefinements()->exists();
    }

    public function shouldShowModificationInProgressBar(): bool
    {
        if ($this->hasCompletedManufacturing()) {
            return false;
        }

        return $this->hasActiveModificationForAny() || $this->hasModificationHistory();
    }

    public function shouldShowRefinementInProgressBar(): bool
    {
        return $this->hasCompletedManufacturing()
            || $this->hasActiveRefinement()
            || $this->hasRefinementHistory();
    }

    /**
     * Doctor may request modification on the current pending stage (before approve)
     * or on an approved stage to start a new modification cycle.
     */
    public function canRequestModification(?int $stageNumber = null): bool
    {
        if ($this->hasCompletedManufacturing()) {
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

            if ($plan === null || ! $plan->is_current) {
                return false;
            }

            if ($plan->isPending() && $this->doctorReviewStageNumber() === $stageNumber) {
                return true;
            }

            return $plan->isApproved();
        }

        $plan = $this->currentFullTreatmentPlan();

        if ($plan === null || ! $plan->is_current) {
            return false;
        }

        if ($plan->isPending()) {
            return true;
        }

        return $plan->isApproved();
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

    public function defaultScanSetKey(): string
    {
        $candidates = $this->caseScanSetCandidates();

        return $candidates[0]['key'] ?? 'original';
    }

    /**
     * @return array<string, list<array{id: int, url: string, name: string, download_url: string}>>
     */
    public function casePhotosGalleryBySet(): array
    {
        $bySet = [];

        foreach ($this->caseScanSetCandidates() as $candidate) {
            $bySet[$candidate['key']] = $this->photosForSetKey($candidate['key'])
                ->map(fn (PatientPhoto $photo) => [
                    'id' => $photo->id,
                    'url' => $photo->url(),
                    'name' => $photo->original_name ?: basename($photo->path),
                    'download_url' => route('patients.photos.download', [$this, $photo]),
                ])
                ->values()
                ->all();
        }

        return $bySet;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PatientPhoto>
     */
    public function photosForSetKey(string $setKey)
    {
        return $this->photos()->forSetKey($setKey)->orderBy('sort_order')->get();
    }

    /**
     * @return list<array{key: string, label: string, notes: ?string, files: list<array>, photo_count: int}>
     */
    public function caseScanSetsForViewer(): array
    {
        return array_map(function (array $candidate) {
            return [
                'key' => $candidate['key'],
                'label' => $candidate['label'],
                'notes' => $candidate['notes'],
                'files' => $candidate['files'],
                'photo_count' => $this->photosForSetKey($candidate['key'])->count(),
            ];
        }, $this->caseScanSetCandidates());
    }

    /**
     * Latest action first (refinement, modification, or original case).
     *
     * @return list<array{key: string, label: string, notes: ?string, files: list<array>, at: \Carbon\Carbon}>
     */
    protected function caseScanSetCandidates(): array
    {
        $candidates = [];

        $originalFiles = $this->caseScanFiles();
        $originalPhotoCount = $this->photosForSetKey('original')->count();

        if (count($originalFiles) > 0 || $originalPhotoCount > 0) {
            $candidates[] = [
                'key' => 'original',
                'label' => 'Original case data',
                'notes' => null,
                'files' => $originalFiles,
                'at' => $this->created_at ?? now(),
            ];
        }

        if (Schema::hasTable('patient_case_modifications')) {
            foreach ($this->caseModifications()->orderBy('version')->get() as $mod) {
                $files = $mod->caseScanFiles();
                $photoCount = $mod->photos()->count();

                if (count($files) === 0 && $photoCount === 0) {
                    continue;
                }

                $candidates[] = [
                    'key' => 'mod-'.$mod->id,
                    'label' => $mod->scopeLabel(),
                    'notes' => $mod->notes,
                    'files' => $files,
                    'at' => $mod->created_at ?? now(),
                ];
            }
        }

        if (Schema::hasTable('patient_case_refinements')) {
            foreach ($this->caseRefinements()->orderBy('version')->get() as $ref) {
                $files = $ref->caseScanFiles();
                $photoCount = $ref->photos()->count();

                if (count($files) === 0 && $photoCount === 0) {
                    continue;
                }

                $candidates[] = [
                    'key' => 'ref-'.$ref->id,
                    'label' => $ref->scopeLabel(),
                    'notes' => $ref->notes,
                    'files' => $files,
                    'at' => $ref->created_at ?? now(),
                ];
            }
        }

        usort($candidates, fn (array $a, array $b) => $b['at'] <=> $a['at']);

        return $candidates;
    }

    public function defaultTreatmentPlanContextKey(): string
    {
        $contexts = $this->treatmentPlanContextsForViewer();

        return $contexts[0]['key'] ?? 'original';
    }

    /**
     * Treatment plan contexts for the version switcher (original, modifications, refinements).
     *
     * @return list<array<string, mixed>>
     */
    public function treatmentPlanContextsForViewer(): array
    {
        $contexts = [];

        $originalFullPlans = $this->visibleFullTreatmentPlansForOriginalCycle();
        $originalStagePlans = $this->originalCycleStageTreatmentPlans();
        $originalStageNumbers = $this->originalCycleTreatmentPlanStageNumbers();

        $latestOriginalAt = $this->originalCycleTreatmentPlansQuery()->max('updated_at');

        $contexts[] = $this->buildTreatmentPlanContext(
            'original',
            'Original case plan',
            'original',
            $latestOriginalAt ? \Carbon\Carbon::parse($latestOriginalAt) : ($this->created_at ?? now()),
            ! $this->hasActiveRefinement() && ! $this->hasActiveModificationForAny(),
            [
                'visible_full_plans' => $originalFullPlans,
                'full_plan' => $this->originalCycleFullTreatmentPlan(),
                'stage_plans' => $originalStagePlans,
                'stage_numbers' => $originalStageNumbers,
            ]
        );

        if (Schema::hasTable('patient_case_modifications')) {
            foreach ($this->caseModifications()->with('treatmentPlan')->orderBy('version')->get() as $mod) {
                if (! $mod->is_current && ! $mod->hasRevisedPlan()) {
                    continue;
                }

                $linkedPlan = $mod->treatmentPlan;
                $planUrl = $mod->hasRevisedPlan()
                    ? $mod->revised_plan_url
                    : ($linkedPlan?->plan_url);
                $reviewStatus = $mod->is_current && $linkedPlan?->is_current
                    ? $linkedPlan->review_status
                    : ($mod->hasRevisedPlan() ? 'approved' : 'pending');

                $contexts[] = $this->buildTreatmentPlanContext(
                    'mod-'.$mod->id,
                    $mod->scopeLabel(),
                    'modification',
                    $mod->hasRevisedPlan()
                        ? ($mod->updated_at ?? $mod->created_at ?? now())
                        : ($mod->created_at ?? now()),
                    $mod->is_current,
                    [
                        'modification_id' => $mod->id,
                        'modification' => $mod,
                        'plan_url' => $planUrl,
                        'review_status' => $reviewStatus,
                        'treatment_plan' => $mod->is_current ? $linkedPlan : null,
                        'stage_number' => $mod->stage_number,
                    ]
                );
            }
        }

        if (Schema::hasTable('patient_case_refinements')) {
            foreach ($this->caseRefinements()->orderBy('version')->get() as $ref) {
                $refStageNumbers = $this->treatmentPlanStageNumbersForRefinement($ref->id);
                $refFullPlans = $this->visibleFullTreatmentPlansForRefinement($ref->id);
                $refStagePlans = $this->currentStageTreatmentPlansForRefinement($ref->id);

                if ($refFullPlans->isEmpty()
                    && $refStagePlans->isEmpty()
                    && ! $ref->is_current) {
                    continue;
                }

                $latestRefAt = $this->treatmentPlans()
                    ->where('refinement_id', $ref->id)
                    ->max('updated_at');

                $contexts[] = $this->buildTreatmentPlanContext(
                    'ref-'.$ref->id,
                    $ref->scopeLabel(),
                    'refinement',
                    $latestRefAt
                        ? \Carbon\Carbon::parse($latestRefAt)
                        : ($ref->created_at ?? now()),
                    $ref->is_current,
                    [
                        'refinement_id' => $ref->id,
                        'refinement' => $ref,
                        'visible_full_plans' => $refFullPlans,
                        'full_plan' => $refFullPlans->firstWhere('is_current', true)
                            ?? $refFullPlans->sortByDesc('version')->first(),
                        'stage_plans' => $refStagePlans,
                        'stage_numbers' => $refStageNumbers,
                    ]
                );
            }
        }

        usort($contexts, fn (array $a, array $b) => $b['at'] <=> $a['at']);

        return $contexts;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function buildTreatmentPlanContext(
        string $key,
        string $label,
        string $type,
        $at,
        bool $isActive,
        array $payload
    ): array {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'at' => $at,
            'is_active' => $isActive,
        ], $payload);
    }

    /**
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleFullTreatmentPlansForOriginalCycle()
    {
        $plans = $this->originalCycleTreatmentPlansQuery()
            ->whereNull('stage_number')
            ->orderBy('version')
            ->get();

        return $this->visibleTreatmentPlanVersions($plans);
    }

    /**
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleFullTreatmentPlansForRefinement(int $refinementId)
    {
        $plans = $this->treatmentPlans()
            ->where('refinement_id', $refinementId)
            ->whereNull('stage_number')
            ->orderBy('version')
            ->get();

        return $this->visibleTreatmentPlanVersions($plans);
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function originalCycleTreatmentPlanStageNumbers()
    {
        return $this->originalCycleTreatmentPlansQuery()
            ->whereNotNull('stage_number')
            ->distinct()
            ->orderBy('stage_number')
            ->pluck('stage_number')
            ->map(fn ($n) => (int) $n)
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function treatmentPlanStageNumbersForRefinement(int $refinementId)
    {
        return $this->treatmentPlans()
            ->where('refinement_id', $refinementId)
            ->whereNotNull('stage_number')
            ->distinct()
            ->orderBy('stage_number')
            ->pluck('stage_number')
            ->map(fn ($n) => (int) $n)
            ->values();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PatientTreatmentPlan>
     */
    public function currentStageTreatmentPlansForRefinement(int $refinementId)
    {
        return $this->treatmentPlans()
            ->where('refinement_id', $refinementId)
            ->whereNotNull('stage_number')
            ->where('is_current', true)
            ->orderBy('stage_number')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleTreatmentPlansForStageInOriginalCycle(int $stageNumber)
    {
        $plans = $this->originalCycleTreatmentPlansQuery()
            ->where('stage_number', $stageNumber)
            ->orderBy('version')
            ->get();

        return $this->visibleTreatmentPlanVersions($plans);
    }

    /**
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleTreatmentPlansForStageInRefinement(int $refinementId, int $stageNumber)
    {
        $plans = $this->treatmentPlans()
            ->where('refinement_id', $refinementId)
            ->where('stage_number', $stageNumber)
            ->orderBy('version')
            ->get();

        return $this->visibleTreatmentPlanVersions($plans);
    }

    /**
     * Treatment plan versions shown on the Treatment Plan tab (reject/revise cycles only).
     *
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    protected function visibleTreatmentPlanVersions($plans)
    {
        return $plans->filter(function (PatientTreatmentPlan $plan) use ($plans) {
            if ((int) $plan->version <= 1) {
                return true;
            }

            $previous = $plans->firstWhere('version', (int) $plan->version - 1);

            return $previous !== null && $previous->isRejected();
        })->values();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PatientCaseModification>
     */
    public function modificationRecords()
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return collect();
        }

        return $this->caseModifications()
            ->with(['requester', 'photos'])
            ->orderBy('version')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PatientCaseRefinement>
     */
    public function refinementRecords()
    {
        if (! Schema::hasTable('patient_case_refinements')) {
            return collect();
        }

        return $this->caseRefinements()
            ->with(['requester', 'photos', 'treatmentPlans'])
            ->orderBy('version')
            ->get();
    }

    /**
     * All full-case treatment plan versions in the active cycle (for version switcher).
     *
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleFullTreatmentPlans()
    {
        $plans = $this->treatmentPlansQuery()
            ->whereNull('stage_number')
            ->orderBy('version')
            ->get();

        return $this->visibleTreatmentPlanVersions($plans);
    }

    /**
     * All treatment plan versions for a stage in the active cycle (for version switcher).
     *
     * @return \Illuminate\Support\Collection<int, PatientTreatmentPlan>
     */
    public function visibleTreatmentPlansForStage(int $stageNumber)
    {
        $plans = $this->treatmentPlansQuery()
            ->where('stage_number', $stageNumber)
            ->orderBy('version')
            ->get();

        return $this->visibleTreatmentPlanVersions($plans);
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
            'modification' => 'modification',
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

        if (! $this->shouldShowModificationInProgressBar()) {
            $steps = array_values(array_filter(
                $steps,
                fn (array $step) => $step['key'] !== 'modification'
            ));
        }

        if (! $this->shouldShowRefinementInProgressBar()) {
            $steps = array_values(array_filter(
                $steps,
                fn (array $step) => $step['key'] !== 'refinement'
            ));
        }

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
                if ($index === $currentIndex && $this->isAwaitingNextDividedStageAfterSingleApproval()) {
                    $state = 'current';
                    $label = 'Stage approved · add next stage';
                } elseif ($index === $currentIndex && $internalKey === 'waiting_plan' && $planOverlay === null) {
                    $label = 'Awaiting treatment plan';
                } elseif ($index < $currentIndex) {
                    $label = 'Treatment plan ready';
                }
            }

            if ($key === 'modification') {
                if ($index === $currentIndex) {
                    $state = 'current';
                    $variant = 'modification';
                    $label = 'Awaiting new plan';
                } elseif ($index < $currentIndex) {
                    $state = 'completed';
                    $label = 'Modification done';
                    $variant = 'modification';
                }
            }

            if ($key === 'case_status') {
                if ($planOverlay === 'pending' && $index === $currentIndex) {
                    $state = 'current';
                    $label = $this->hasActiveModificationForAny()
                        ? 'Awaiting doctor approval · After mod'
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
                    $label = 'Treatment plan Manufactured';
                } elseif ($internalKey === 'approved' && $index === $currentIndex) {
                    $state = 'current';
                    $label = 'Approved for manufacture';
                } elseif ($index < $currentIndex && $this->hasCompletedManufacturing()) {
                    $state = 'completed';
                    $label = 'Treatment plan Manufactured';
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
                'tone' => $this->workflowStepTone($key, $state, $variant),
            ];

            if ($variant !== null) {
                $row['variant'] = $variant;
            }

            return $row;
        })->all();
    }

    public function workflowProgressPercent(): float
    {
        $steps = $this->workflowProgress();
        $total = count($steps);

        if ($total <= 1) {
            return 0.0;
        }

        $currentIndex = 0;

        foreach ($steps as $i => $step) {
            if (in_array($step['state'], ['current', 'rejected'], true)) {
                $currentIndex = $i;
                break;
            }
        }

        return round(($currentIndex / ($total - 1)) * 100, 1);
    }

    private function workflowStepTone(string $key, string $state, ?string $variant): string
    {
        if ($state === 'rejected') {
            return 'rejected';
        }

        if ($variant === 'no-mod' || $state === 'skipped') {
            return 'skipped';
        }

        if ($variant === 'modification') {
            return 'modification';
        }

        if (in_array($variant, ['refinement', 'refinement-review'], true)) {
            return 'refinement';
        }

        return match ($key) {
            'created' => 'created',
            'waiting_plan' => 'plan',
            'case_status' => 'review',
            'modification' => 'modification',
            'approved' => 'approved',
            'refinement' => 'refinement',
            default => 'default',
        };
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

    public function patientAge(): ?int
    {
        if ($this->age !== null) {
            return (int) $this->age;
        }

        if ($this->date_of_birth) {
            return $this->date_of_birth->age;
        }

        return null;
    }

    public function genderAgeLabel(): string
    {
        $gender = match ($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            default => '—',
        };

        return $gender.' / '.($this->patientAge() ?? 0).' Years';
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

    public static function normalizePatientId(?string $patientId): string
    {
        $id = trim((string) $patientId);

        return trim(preg_replace('/^LA\s+/i', '', $id)) ?: $id;
    }

    public function getDisplayPatientIdAttribute(): string
    {
        return static::normalizePatientId($this->patient_id);
    }

    public static function generatePatientId(): string
    {
        $number = str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);

        return $number;
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
