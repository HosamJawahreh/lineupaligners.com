<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientCaseModification;
use App\Models\PatientCaseRefinement;
use App\Models\PatientManufacturingStage;
use App\Models\PatientTreatmentPlan;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CaseTimelineBuilder
{
    /**
     * @return array{events: list<array<string, mixed>>, grouped: array<string, list<array<string, mixed>>>}
     */
    public function build(Patient $patient): array
    {
        $events = collect();

        if ($patient->created_at) {
            $events->push($this->caseCreatedEvent($patient));
        }

        if ($patient->has3dScans() && $patient->created_at) {
            $events->push($this->initialScansEvent($patient));
        }

        $patient->loadMissing([
            'treatmentPlans.uploader',
            'treatmentPlans.reviewer',
            'treatmentPlans.manufacturedByUser',
            'caseModifications.requester',
            'caseModifications.treatmentPlan.uploader',
            'manufacturedByUser',
            'doctor',
        ]);

        if (Schema::hasTable('patient_case_refinements')) {
            $patient->loadMissing(['caseRefinements.requester']);
            foreach ($patient->caseRefinements->sortBy('created_at') as $refinement) {
                $events->push($this->refinementEvent($refinement));
            }
        }

        foreach ($patient->treatmentPlans->sortBy('created_at') as $plan) {
            $events->push($this->planUploadedEvent($plan));
            if ($plan->reviewed_at) {
                $events->push($this->planReviewedEvent($plan));
            }
            if ($plan->manufactured_at && $plan->stage_number !== null) {
                $events->push($this->stageManufacturedEvent($plan));
            }
        }

        if (Schema::hasTable('patient_manufacturing_stages')) {
            $patient->loadMissing(['manufacturingStages.manufacturedByUser']);
            foreach ($patient->manufacturingStages->sortBy('stage_number') as $stage) {
                $events->push($this->manufacturingStageEvent($stage));
            }
        }

        foreach ($patient->caseModifications->sortBy('created_at') as $modification) {
            $events->push($this->modificationEvent($modification));
            if ($modification->hasRevisedPlan()) {
                $events->push($this->modificationPlanUploadedEvent($modification));
            }
        }

        if ($patient->manufactured_at) {
            $events->push($this->manufacturedEvent($patient));
        }

        return $this->finalizeTimeline($events);
    }

    /**
     * @return array{events: list<array<string, mixed>>, grouped: array<string, list<array<string, mixed>>>}
     */
    public function buildModificationHistory(Patient $patient): array
    {
        $patient->loadMissing(['treatmentPlans', 'caseModifications']);

        return $this->buildFiltered($patient, function (array $event) use ($patient): bool {
            if (in_array($event['type'], ['modification_requested', 'modification_plan_uploaded', 'plan_rejected'], true)) {
                return true;
            }

            $plan = $this->planFromEventId($patient, $event['id'] ?? '');

            if ($plan === null || $plan->refinement_id !== null) {
                return false;
            }

            if ($event['type'] === 'plan_uploaded') {
                return $plan->version > 1;
            }

            if ($event['type'] === 'plan_approved') {
                return $plan->version > 1
                    || $patient->caseModifications->contains('treatment_plan_id', $plan->id);
            }

            return false;
        });
    }

    /**
     * @return array{events: list<array<string, mixed>>, grouped: array<string, list<array<string, mixed>>>}
     */
    public function buildRefinementHistory(Patient $patient): array
    {
        $patient->loadMissing(['treatmentPlans', 'caseRefinements']);

        return $this->buildFiltered($patient, function (array $event) use ($patient): bool {
            if ($event['type'] === 'refinement_ordered') {
                return true;
            }

            $plan = $this->planFromEventId($patient, $event['id'] ?? '');

            if ($plan !== null && $plan->refinement_id !== null) {
                return in_array($event['type'], ['plan_uploaded', 'plan_approved', 'plan_rejected'], true);
            }

            return false;
        });
    }

    /**
     * @param  callable(array<string, mixed>): bool  $filter
     * @return array{events: list<array<string, mixed>>, grouped: array<string, list<array<string, mixed>>>}
     */
    protected function buildFiltered(Patient $patient, callable $filter): array
    {
        $events = collect($this->build($patient)['events'])->filter($filter)->values();

        return $this->finalizeTimeline($events);
    }

    /**
     * @param  Collection<int, array<string, mixed>>|list<array<string, mixed>>  $events
     * @return array{events: list<array<string, mixed>>, grouped: array<string, list<array<string, mixed>>>}
     */
    protected function finalizeTimeline(Collection|array $events): array
    {
        $sorted = collect($events)
            ->filter(fn (?array $e) => $e !== null && ! empty($e['occurred_at']))
            ->sortByDesc(fn (array $e) => $e['occurred_at']->timestamp)
            ->values()
            ->map(function (array $event, int $index) {
                $event['is_latest'] = $index === 0;
                $at = $event['occurred_at'];
                $event['date_label'] = $at->format('M j, Y');
                $event['time_label'] = $at->format('g:i A');
                $event['date_key'] = $at->format('Y-m-d');

                return $event;
            })
            ->all();

        $grouped = collect($sorted)
            ->groupBy('date_key')
            ->map(fn (Collection $group) => $group->values()->all())
            ->all();

        return [
            'events' => $sorted,
            'grouped' => $grouped,
        ];
    }

    protected function planFromEventId(Patient $patient, string $eventId): ?PatientTreatmentPlan
    {
        if (! preg_match('/^plan-(?:upload|review)-(\d+)$/', $eventId, $matches)) {
            return null;
        }

        return $patient->treatmentPlans->firstWhere('id', (int) $matches[1]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function latestEvent(Patient $patient): ?array
    {
        $events = $this->build($patient)['events'];

        return $events[0] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function caseCreatedEvent(Patient $patient): array
    {
        $doctor = $patient->doctor?->fullName();

        return $this->event(
            id: 'case-created',
            type: 'case_created',
            at: $patient->created_at,
            title: 'Case opened',
            summary: $patient->caseTypeLabel().' · '.$patient->display_patient_id,
            body: null,
            actorName: $doctor ? 'Dr. '.$doctor : null,
            actorRole: 'Doctor',
            tone: 'slate',
            icon: 'zmdi-folder-star',
            badges: [['label' => 'New case', 'variant' => 'neutral']],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function initialScansEvent(Patient $patient): array
    {
        $parts = array_filter([
            $patient->upper_jaw_scan ? 'Upper jaw' : null,
            $patient->lower_jaw_scan ? 'Lower jaw' : null,
        ]);

        return $this->event(
            id: 'initial-scans',
            type: 'scans_initial',
            at: $patient->created_at,
            title: 'Initial 3D scans uploaded',
            summary: implode(' & ', $parts) ?: '3D models attached',
            body: null,
            actorName: null,
            actorRole: null,
            tone: 'violet',
            icon: 'zmdi-rotate-3d',
            badges: [['label' => '3D Scans & Photos', 'variant' => 'violet']],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function planUploadedEvent(PatientTreatmentPlan $plan): array
    {
        $isRevision = $plan->version > 1;
        $badges = [
            ['label' => 'Version '.$plan->version, 'variant' => 'neutral'],
        ];

        if ($plan->is_current && $plan->isPending()) {
            $badges[] = ['label' => 'Awaiting review', 'variant' => 'pending'];
        }

        if ($plan->stage_number !== null) {
            $badges[] = ['label' => 'Stage '.$plan->stage_number, 'variant' => 'stage'];
        }

        if ($plan->refinement_id) {
            $badges[] = ['label' => 'Refinement cycle', 'variant' => 'violet'];
        }

        return $this->event(
            id: 'plan-upload-'.$plan->id,
            type: 'plan_uploaded',
            at: $plan->created_at,
            title: $plan->refinement_id
                ? 'Refinement treatment plan uploaded'
                : ($isRevision ? 'Revised treatment plan submitted' : 'Treatment plan uploaded'),
            summary: $plan->stageLabel().' · View on Treatment Plan tab',
            body: null,
            actorName: $plan->uploader?->displayName(),
            actorRole: 'LineUp Admin',
            tone: 'blue',
            icon: 'zmdi-link',
            badges: $badges,
            isActive: $plan->is_current && $plan->isPending(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function planReviewedEvent(PatientTreatmentPlan $plan): array
    {
        $approved = $plan->isApproved();

        return $this->event(
            id: 'plan-review-'.$plan->id,
            type: $approved ? 'plan_approved' : 'plan_rejected',
            at: $plan->reviewed_at,
            title: $approved ? 'Treatment plan approved' : 'Modification ordered',
            summary: $plan->stageLabel().' · Version '.$plan->version,
            body: $plan->review_comment,
            actorName: $plan->reviewer?->displayName(),
            actorRole: 'Doctor',
            tone: $approved ? 'green' : 'rose',
            icon: $approved ? 'zmdi-check-circle' : 'zmdi-close-circle',
            badges: [
                ['label' => $plan->reviewStatusLabel(), 'variant' => $approved ? 'success' : 'danger'],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function modificationEvent(PatientCaseModification $mod): array
    {
        $files = array_filter([
            $mod->upper_jaw_scan ? 'Upper' : null,
            $mod->lower_jaw_scan ? 'Lower' : null,
        ]);

        $badges = [
            ['label' => $mod->scopeLabel(), 'variant' => 'amber'],
        ];

        if ($mod->is_current) {
            $badges[] = ['label' => 'In progress', 'variant' => 'pending'];
        }

        return $this->event(
            id: 'mod-'.$mod->id,
            type: 'modification_requested',
            at: $mod->created_at,
            title: 'Modification requested',
            summary: 'New 3D scans: '.(implode(' · ', $files) ?: '—'),
            body: $mod->notes,
            actorName: $mod->requester?->displayName(),
            actorRole: 'Doctor',
            tone: 'amber',
            icon: 'zmdi-edit',
            badges: $badges,
            isActive: $mod->is_current,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function modificationPlanUploadedEvent(PatientCaseModification $mod): array
    {
        $plan = $mod->treatmentPlan;
        $badges = [
            ['label' => $mod->scopeLabel(), 'variant' => 'amber'],
        ];

        if ($plan?->is_current && $plan->isPending()) {
            $badges[] = ['label' => 'Awaiting review', 'variant' => 'pending'];
        }

        return $this->event(
            id: 'mod-plan-'.$mod->id,
            type: 'modification_plan_uploaded',
            at: $plan?->updated_at ?? $mod->updated_at,
            title: 'Modified treatment plan uploaded',
            summary: $mod->scopeLabel().' · View on Treatment Plan tab',
            body: null,
            actorName: $plan?->uploader?->displayName(),
            actorRole: 'LineUp Admin',
            tone: 'blue',
            icon: 'zmdi-assignment-check',
            badges: $badges,
            isActive: (bool) ($mod->is_current && $plan?->is_current && $plan->isPending()),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function stageManufacturedEvent(PatientTreatmentPlan $plan): array
    {
        $badges = [
            ['label' => $plan->stageLabel(), 'variant' => 'stage'],
            ['label' => 'Manufactured', 'variant' => 'success'],
        ];

        if ($plan->manufacturedStepRangeLabel() !== '') {
            $badges[] = ['label' => $plan->manufacturedStepRangeLabel(), 'variant' => 'neutral'];
        }

        return $this->event(
            id: 'stage-mfg-'.$plan->id,
            type: 'stage_manufactured',
            at: $plan->manufactured_at,
            title: 'Stage '.$plan->stage_number.' manufactured',
            summary: $plan->manufacturedStepRangeLabel() ?: $plan->stageLabel(),
            body: null,
            actorName: $plan->manufacturedByUser?->displayName(),
            actorRole: 'LineUp Admin',
            tone: 'emerald',
            icon: 'zmdi-check-circle',
            badges: $badges,
        );
    }

    protected function manufacturingStageEvent(PatientManufacturingStage $stage): array
    {
        $badges = [
            ['label' => 'Stage '.$stage->stage_number, 'variant' => 'stage'],
            ['label' => 'Manufactured', 'variant' => 'success'],
            ['label' => $stage->stepRangeLabel(), 'variant' => 'neutral'],
        ];

        return $this->event(
            id: 'mfg-stage-'.$stage->id,
            type: 'stage_manufactured',
            at: $stage->manufactured_at,
            title: 'Manufacturing stage '.$stage->stage_number.' recorded',
            summary: $stage->stepRangeLabel(),
            body: null,
            actorName: $stage->manufacturedByUser?->displayName(),
            actorRole: 'LineUp Admin',
            tone: 'emerald',
            icon: 'zmdi-check-circle',
            badges: $badges,
        );
    }

    protected function manufacturedEvent(Patient $patient): array
    {
        $isCurrent = $patient->isManufactured();

        return $this->event(
            id: 'manufactured-'.$patient->id.'-'.$patient->manufactured_at->timestamp,
            type: 'case_manufactured',
            at: $patient->manufactured_at,
            title: 'Case marked manufactured',
            summary: $patient->caseTypeLabel().' · cycle complete',
            body: null,
            actorName: $patient->manufacturedByUser?->displayName(),
            actorRole: 'LineUp Admin',
            tone: 'emerald',
            icon: 'zmdi-check-circle',
            badges: [
                ['label' => 'Manufactured', 'variant' => 'success'],
                ['label' => 'Case complete', 'variant' => 'emerald'],
            ],
            isActive: $isCurrent,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function refinementEvent(PatientCaseRefinement $ref): array
    {
        $files = array_filter([
            $ref->upper_jaw_scan ? 'Upper' : null,
            $ref->lower_jaw_scan ? 'Lower' : null,
        ]);

        $badges = [
            ['label' => $ref->scopeLabel(), 'variant' => 'violet'],
        ];

        if ($ref->is_current) {
            $badges[] = ['label' => 'In progress', 'variant' => 'pending'];
        }

        return $this->event(
            id: 'ref-'.$ref->id,
            type: 'refinement_ordered',
            at: $ref->created_at,
            title: 'Refinement ordered',
            summary: 'New 3D scans: '.(implode(' · ', $files) ?: '—'),
            body: $ref->notes,
            actorName: $ref->requester?->displayName(),
            actorRole: 'Doctor',
            tone: 'violet',
            icon: 'zmdi-swap-vertical',
            badges: $badges,
            isActive: $ref->is_current,
        );
    }

    /**
     * @param  list<array{label: string, variant: string}>  $badges
     * @return array<string, mixed>
     */
    protected function event(
        string $id,
        string $type,
        ?CarbonInterface $at,
        string $title,
        ?string $summary,
        ?string $body,
        ?string $actorName,
        ?string $actorRole,
        string $tone,
        string $icon,
        array $badges = [],
        bool $isActive = false,
    ): array {
        return [
            'id' => $id,
            'type' => $type,
            'occurred_at' => $at,
            'title' => $title,
            'summary' => $summary,
            'body' => $body,
            'actor_name' => $actorName,
            'actor_role' => $actorRole,
            'tone' => $tone,
            'icon' => $icon,
            'badges' => $badges,
            'is_active' => $isActive,
            'is_latest' => false,
            'date_label' => '',
            'time_label' => '',
            'date_key' => '',
        ];
    }

    protected function truncateUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (strlen($url) <= 72) {
            return $url;
        }

        return substr($url, 0, 69).'…';
    }
}
