<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientCaseModification;
use App\Models\PatientCaseRefinement;
use App\Models\PatientPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CasePhotoStorage
{
    public const MAX_KB = 102400;

    public function storeFromRequest(Request $request, Patient $patient, ?PatientCaseModification $modification = null, ?PatientCaseRefinement $refinement = null): int
    {
        $uploads = $request->file('photos');

        if ($uploads === null) {
            return 0;
        }

        if (! is_array($uploads)) {
            $uploads = [$uploads];
        }

        $uploads = array_values(array_filter(
            $uploads,
            fn ($file) => $file instanceof UploadedFile && $file->isValid()
        ));

        if ($uploads === []) {
            return 0;
        }

        $sort = (int) PatientPhoto::query()
            ->where('patient_id', $patient->id)
            ->when($modification, fn ($q) => $q->where('modification_id', $modification->id))
            ->when($refinement, fn ($q) => $q->where('refinement_id', $refinement->id))
            ->when(! $modification && ! $refinement, fn ($q) => $q->whereNull('modification_id')->whereNull('refinement_id'))
            ->max('sort_order');

        $subdir = $this->storageSubdir($patient, $modification, $refinement);
        $stored = 0;

        foreach ($uploads as $file) {
            $path = $file->store($subdir, 'public');
            $patient->photos()->create([
                'modification_id' => $modification?->id,
                'refinement_id' => $refinement?->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'sort_order' => ++$sort,
            ]);
            $stored++;
        }

        if (! $modification && ! $refinement) {
            $this->syncPrimaryPhoto($patient);
        }

        return $stored;
    }

    public function syncPrimaryPhoto(Patient $patient): void
    {
        $first = $patient->originalPhotos()->orderBy('sort_order')->first();
        $patient->update(['photo' => $first?->path]);
    }

    public function deletePhotoFile(PatientPhoto $photo): void
    {
        if ($photo->path && Storage::disk('public')->exists($photo->path)) {
            Storage::disk('public')->delete($photo->path);
        }
    }

    private function storageSubdir(Patient $patient, ?PatientCaseModification $modification, ?PatientCaseRefinement $refinement): string
    {
        if ($modification) {
            return "patients/{$patient->id}/modifications/{$modification->id}/photos";
        }

        if ($refinement) {
            return "patients/{$patient->id}/refinements/{$refinement->id}/photos";
        }

        return "patients/{$patient->id}/photos";
    }
}
