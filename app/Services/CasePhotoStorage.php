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

    public function storeFromRequest(Request $request, Patient $patient, ?PatientCaseModification $modification = null, ?PatientCaseRefinement $refinement = null): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        $sort = (int) PatientPhoto::query()
            ->where('patient_id', $patient->id)
            ->when($modification, fn ($q) => $q->where('modification_id', $modification->id))
            ->when($refinement, fn ($q) => $q->where('refinement_id', $refinement->id))
            ->when(! $modification && ! $refinement, fn ($q) => $q->whereNull('modification_id')->whereNull('refinement_id'))
            ->max('sort_order');

        $subdir = $this->storageSubdir($patient, $modification, $refinement);

        foreach ($request->file('photos') as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store($subdir, 'public');
            $patient->photos()->create([
                'modification_id' => $modification?->id,
                'refinement_id' => $refinement?->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'sort_order' => ++$sort,
            ]);
        }

        if (! $modification && ! $refinement) {
            $this->syncPrimaryPhoto($patient);
        }
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
