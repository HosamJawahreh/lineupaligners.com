<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('theme.pages.all-departments');
    }

    public function create(): View
    {
        return view('theme.pages.add-departments');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Department::create($data);

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function show(Department $department): View
    {
        return view('theme.pages.more-departments');
    }

    public function edit(Department $department): View
    {
        return view('theme.pages.add-departments');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]));

        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
