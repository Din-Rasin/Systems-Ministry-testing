<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Department;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display a listing of the holidays.
     */
    public function index()
    {
        $holidays = Holiday::with('department')->orderBy('year')->orderBy('holiday_date')->get();
        return view('admin.holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        $departments = Department::all();
        return view('admin.holidays.create', compact('departments'));
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'holiday_name' => 'required|string|max:255',
            'holiday_date' => 'required|date',
            'holiday_type' => 'required|in:PUBLIC,COMPANY,DEPARTMENT',
            'department_id' => 'nullable|exists:departments,id',
            'country_code' => 'nullable|string|max:10',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
            'year' => 'required|integer',
        ]);

        // If it's a department holiday, department_id is required
        if ($validated['holiday_type'] === 'DEPARTMENT' && empty($validated['department_id'])) {
            return back()->withErrors(['department_id' => 'Department is required for department holidays.']);
        }

        // If it's a public holiday, department_id should be null
        if ($validated['holiday_type'] === 'PUBLIC') {
            $validated['department_id'] = null;
        }

        Holiday::create($validated);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    /**
     * Show the form for editing the specified holiday.
     */
    public function edit(Holiday $holiday)
    {
        $departments = Department::all();
        return view('admin.holidays.edit', compact('holiday', 'departments'));
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'holiday_name' => 'required|string|max:255',
            'holiday_date' => 'required|date',
            'holiday_type' => 'required|in:PUBLIC,COMPANY,DEPARTMENT',
            'department_id' => 'nullable|exists:departments,id',
            'country_code' => 'nullable|string|max:10',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
            'year' => 'required|integer',
        ]);

        // If it's a department holiday, department_id is required
        if ($validated['holiday_type'] === 'DEPARTMENT' && empty($validated['department_id'])) {
            return back()->withErrors(['department_id' => 'Department is required for department holidays.']);
        }

        // If it's a public holiday, department_id should be null
        if ($validated['holiday_type'] === 'PUBLIC') {
            $validated['department_id'] = null;
        }

        $holiday->update($validated);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Toggle the active status of a holiday.
     */
    public function toggleActive(Holiday $holiday)
    {
        $holiday->update(['is_active' => !$holiday->is_active]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday status updated successfully.');
    }
}
