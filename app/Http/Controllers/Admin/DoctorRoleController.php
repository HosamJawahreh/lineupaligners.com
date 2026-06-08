<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DoctorRoleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);

        DoctorRole::create([
            'name' => $data['name'],
            'slug' => DoctorRole::generateSlug($data['name']),
            'description' => $data['description'] ?? null,
            'permissions' => DoctorRole::normalizePermissions($request->input('permissions', [])),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (DoctorRole::max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('settings.index', ['tab' => 'doctor-roles'])
            ->with('success', 'Doctor role created successfully.');
    }

    public function update(Request $request, DoctorRole $doctorRole): RedirectResponse
    {
        $data = $this->validateRole($request, $doctorRole);

        $doctorRole->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => DoctorRole::normalizePermissions($request->input('permissions', [])),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('settings.index', ['tab' => 'doctor-roles'])
            ->with('success', 'Doctor role updated successfully.');
    }

    public function destroy(DoctorRole $doctorRole): RedirectResponse
    {
        if ($doctorRole->doctors()->exists()) {
            return redirect()->route('settings.index', ['tab' => 'doctor-roles'])
                ->with('error', 'Cannot delete a role that is assigned to doctors.');
        }

        $doctorRole->delete();

        return redirect()->route('settings.index', ['tab' => 'doctor-roles'])
            ->with('success', 'Doctor role removed successfully.');
    }

    private function validateRole(Request $request, ?DoctorRole $role = null): array
    {
        $permissionKeys = array_keys(config('doctor-permissions.permissions', []));

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($permissionKeys)],
            'is_active' => ['nullable', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray())
                ->redirectTo(route('settings.index', ['tab' => 'doctor-roles']));
        }

        return $validator->validated();
    }
}
