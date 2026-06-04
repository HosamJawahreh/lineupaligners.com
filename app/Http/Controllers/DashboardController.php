<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTreatmentPlan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return view('dashboard.index', $this->adminPayload($user));
        }

        return view('dashboard.index', $this->doctorPayload($user));
    }

    /**
     * @return array<string, mixed>
     */
    protected function adminPayload(User $user): array
    {
        $readyForManufacture = $this->readyForManufactureQuery()->count();

        return [
            'isAdmin' => true,
            'greeting' => $this->greeting(),
            'userName' => $user->displayName(),
            'clinicName' => Setting::get('clinic_name', config('app.name')),
            'stats' => [
                [
                    'key' => 'total_cases',
                    'label' => 'Total cases',
                    'value' => Patient::count(),
                    'icon' => 'zmdi-folder',
                    'tone' => 'cyan',
                    'href' => route('patients.index'),
                    'hint' => 'All aligner cases',
                ],
                [
                    'key' => 'doctors',
                    'label' => 'Active doctors',
                    'value' => Doctor::where('is_active', true)->count(),
                    'icon' => 'zmdi-account',
                    'tone' => 'blue',
                    'href' => route('doctors.index'),
                    'hint' => 'Clinic partners',
                ],
                [
                    'key' => 'awaiting_review',
                    'label' => 'Awaiting doctor review',
                    'value' => $this->awaitingDoctorReviewQuery()->count(),
                    'icon' => 'zmdi-assignment-check',
                    'tone' => 'amber',
                    'href' => route('patients.index'),
                    'hint' => 'Plans pending approval',
                ],
                [
                    'key' => 'ready_manufacture',
                    'label' => 'Ready to manufacture',
                    'value' => $readyForManufacture,
                    'icon' => 'zmdi-flag',
                    'tone' => 'emerald',
                    'href' => route('patients.index'),
                    'hint' => 'Approved — mark manufactured',
                ],
                [
                    'key' => 'modification',
                    'label' => 'Modifications',
                    'value' => Patient::where('case_workflow_stage', 'modification')->count(),
                    'icon' => 'zmdi-refresh-sync',
                    'tone' => 'orange',
                    'href' => route('patients.index'),
                    'hint' => 'Awaiting revised plans',
                ],
                [
                    'key' => 'manufactured',
                    'label' => 'Manufactured',
                    'value' => Patient::where('case_workflow_stage', 'manufactured')->count(),
                    'icon' => 'zmdi-check-circle',
                    'tone' => 'slate',
                    'href' => route('patients.index'),
                    'hint' => 'Completed production',
                ],
            ],
            'workflowBreakdown' => $this->workflowBreakdown(),
            'casesChart' => $this->casesCreatedChart(),
            'recentCases' => $this->recentCases(),
            'notifications' => $this->recentNotifications($user),
            'quickActions' => [
                ['label' => 'View all cases', 'icon' => 'zmdi-folder', 'href' => route('patients.index'), 'primary' => false],
                ['label' => 'New case', 'icon' => 'zmdi-plus-circle-o', 'href' => route('patients.create'), 'primary' => true],
                ['label' => 'Manage doctors', 'icon' => 'zmdi-account', 'href' => route('doctors.index'), 'primary' => false],
                ['label' => 'System settings', 'icon' => 'zmdi-settings', 'href' => route('settings.index'), 'primary' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function doctorPayload(User $user): array
    {
        $doctor = $user->doctor;
        $caseQuery = $this->scopedPatientsQuery($doctor);

        $awaitingReview = $doctor
            ? $this->awaitingDoctorReviewQuery($caseQuery)->count()
            : 0;

        $totalPatients = $doctor ? $doctor->patients()->count() : 0;
        $inProduction = $doctor
            ? $doctor->patients()->where('case_workflow_stage', 'approved')->count()
            : 0;
        $modificationCount = $doctor
            ? $doctor->patients()->where('case_workflow_stage', 'modification')->count()
            : 0;
        $refinementCount = $doctor
            ? $doctor->patients()->where('case_workflow_stage', 'refinement')->count()
            : 0;
        $manufacturedCount = $doctor
            ? $doctor->patients()->where('case_workflow_stage', 'manufactured')->count()
            : 0;

        return [
            'isAdmin' => false,
            'greeting' => $this->greeting(),
            'userName' => $user->displayName(),
            'clinicName' => $doctor?->clinicNameForDisplay() ?? Setting::get('clinic_name', config('app.name')),
            'stats' => [
                [
                    'key' => 'my_patients',
                    'label' => 'Patients',
                    'value' => $totalPatients,
                    'icon' => 'zmdi-account-circle',
                    'tone' => 'teal',
                    'href' => route('patients.index'),
                    'hint' => 'Total patients under your care',
                ],
                [
                    'key' => 'awaiting_review',
                    'label' => 'Awaiting your review',
                    'value' => $awaitingReview,
                    'icon' => 'zmdi-assignment-check',
                    'tone' => 'amber',
                    'href' => route('patients.index'),
                    'hint' => 'Treatment plans to approve',
                ],
                [
                    'key' => 'modification',
                    'label' => 'Modifications',
                    'value' => $modificationCount,
                    'icon' => 'zmdi-refresh-sync',
                    'tone' => 'orange',
                    'href' => route('patients.index'),
                    'hint' => 'Cases awaiting revised plans',
                ],
                [
                    'key' => 'in_progress',
                    'label' => 'In production',
                    'value' => $inProduction,
                    'icon' => 'zmdi-play-circle',
                    'tone' => 'emerald',
                    'href' => route('patients.index'),
                    'hint' => 'Approved for manufacture',
                ],
                [
                    'key' => 'refinement',
                    'label' => 'Refinements',
                    'value' => $refinementCount,
                    'icon' => 'zmdi-redo',
                    'tone' => 'violet',
                    'href' => route('patients.index'),
                    'hint' => 'Active refinement cycles',
                ],
                [
                    'key' => 'manufactured',
                    'label' => 'Manufactured',
                    'value' => $manufacturedCount,
                    'icon' => 'zmdi-check-circle',
                    'tone' => 'slate',
                    'href' => route('patients.index'),
                    'hint' => 'Ready for refinement when needed',
                ],
            ],
            'workflowBreakdown' => $this->workflowBreakdown($caseQuery->clone()),
            'casesChart' => $this->casesCreatedChart($caseQuery->clone()),
            'recentCases' => $this->recentCases($caseQuery->clone()),
            'notifications' => $this->recentNotifications($user),
            'quickActions' => [
                ['label' => 'My cases', 'icon' => 'zmdi-folder', 'href' => route('patients.index'), 'primary' => false],
                ['label' => 'New case', 'icon' => 'zmdi-plus-circle-o', 'href' => route('patients.create'), 'primary' => true],
                ['label' => 'Profile', 'icon' => 'zmdi-account-circle', 'href' => route('profile.edit'), 'primary' => false],
                ['label' => 'Clinic settings', 'icon' => 'zmdi-settings', 'href' => route('doctor.clinic-settings.edit'), 'primary' => false],
            ],
        ];
    }

    protected function scopedPatientsQuery(?Doctor $doctor = null): Builder
    {
        if ($doctor) {
            return Patient::query()->where('doctor_id', $doctor->id);
        }

        return Patient::query();
    }

    protected function greeting(): string
    {
        $hour = (int) now()->format('G');

        if ($hour < 12) {
            return 'Good morning';
        }

        if ($hour < 17) {
            return 'Good afternoon';
        }

        return 'Good evening';
    }

    protected function awaitingDoctorReviewQuery(?Builder $scope = null): Builder
    {
        $query = $scope ? (clone $scope) : Patient::query();

        return $query->whereHas('treatmentPlans', function (Builder $plan) {
            $plan->where('is_current', true)
                ->where('review_status', PatientTreatmentPlan::STATUS_PENDING);
        });
    }

    protected function readyForManufactureQuery(?Builder $scope = null): Builder
    {
        $query = $scope ? (clone $scope) : Patient::query();

        $query->where('case_workflow_stage', 'approved');

        if (Schema::hasTable('patient_case_modifications')) {
            $query->whereDoesntHave('caseModifications', fn (Builder $m) => $m->where('is_current', true));
        }

        if (Schema::hasTable('patient_case_refinements')) {
            $query->whereDoesntHave('caseRefinements', fn (Builder $r) => $r->where('is_current', true));
        }

        return $query;
    }

    /**
     * @return list<array{stage: string, label: string, count: int}>
     */
    protected function workflowBreakdown(?Builder $scope = null): array
    {
        $query = $scope ? (clone $scope) : Patient::query();

        $counts = $query
            ->selectRaw('case_workflow_stage, COUNT(*) as total')
            ->groupBy('case_workflow_stage')
            ->pluck('total', 'case_workflow_stage');

        $labels = [
            'created' => 'Case created',
            'waiting_plan' => 'Treatment plan',
            'approved' => 'Approved',
            'manufactured' => 'Manufactured',
            'modification' => 'Modification',
            'refinement' => 'Refinement',
        ];

        $order = ['created', 'waiting_plan', 'approved', 'manufactured', 'modification', 'refinement'];

        return collect($order)
            ->map(fn (string $stage) => [
                'stage' => $stage,
                'label' => $labels[$stage] ?? ucfirst(str_replace('_', ' ', $stage)),
                'count' => (int) ($counts[$stage] ?? 0),
            ])
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, count: int, height: int}>
     */
    protected function casesCreatedChart(?Builder $scope = null): array
    {
        $query = $scope ? (clone $scope) : Patient::query();
        $start = now()->subDays(6)->startOfDay();

        $raw = $query
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = collect(range(0, 6))->map(function (int $offset) use ($raw) {
            $date = now()->subDays(6 - $offset)->startOfDay();
            $key = $date->format('Y-m-d');

            return [
                'label' => $date->format('D'),
                'count' => (int) ($raw[$key] ?? 0),
            ];
        });

        $max = max(1, $days->max('count') ?? 1);

        return $days->map(function (array $day) use ($max) {
            $day['height'] = (int) round(($day['count'] / $max) * 100);

            return $day;
        })->all();
    }

    /**
     * @return Collection<int, Patient>
     */
    protected function recentCases(?Builder $scope = null): Collection
    {
        $query = $scope ? (clone $scope) : Patient::query();

        return $query
            ->with('doctor')
            ->latest('created_at')
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, \Illuminate\Notifications\DatabaseNotification>
     */
    protected function recentNotifications(User $user): Collection
    {
        return $user->notifications()
            ->latest()
            ->limit(6)
            ->get();
    }
}
