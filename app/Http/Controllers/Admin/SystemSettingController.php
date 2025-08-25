<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemSettingController extends Controller
{
    /**
     * Display a listing of the system settings.
     */
    public function index()
    {
        $settings = SystemSetting::orderBy('category')->orderBy('setting_key')->get();
        return view('admin.system-settings.index', compact('settings'));
    }

    /**
     * Show the form for editing the specified system setting.
     */
    public function edit(SystemSetting $systemSetting)
    {
        return view('admin.system-settings.edit', compact('systemSetting'));
    }

    /**
     * Update the specified system setting in storage.
     */
    public function update(Request $request, SystemSetting $systemSetting)
    {
        $validated = $request->validate([
            'setting_value' => 'required|string',
            'data_type' => 'required|in:STRING,INTEGER,BOOLEAN,JSON',
            'category' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();

        // Convert setting value based on data type
        if ($validated['data_type'] === 'INTEGER') {
            $validated['setting_value'] = (int) $validated['setting_value'];
        } elseif ($validated['data_type'] === 'BOOLEAN') {
            $validated['setting_value'] = filter_var($validated['setting_value'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        } elseif ($validated['data_type'] === 'JSON') {
            // Validate JSON format
            json_decode($validated['setting_value']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['setting_value' => 'Invalid JSON format']);
            }
        }

        $systemSetting->update($validated);

        return redirect()->route('admin.system-settings.index')
            ->with('success', 'System setting updated successfully.');
    }

    /**
     * Show the form for creating a new system setting.
     */
    public function create()
    {
        return view('admin.system-settings.create');
    }

    /**
     * Store a newly created system setting in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|unique:system_settings',
            'setting_value' => 'required|string',
            'data_type' => 'required|in:STRING,INTEGER,BOOLEAN,JSON',
            'category' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();

        // Convert setting value based on data type
        if ($validated['data_type'] === 'INTEGER') {
            $validated['setting_value'] = (int) $validated['setting_value'];
        } elseif ($validated['data_type'] === 'BOOLEAN') {
            $validated['setting_value'] = filter_var($validated['setting_value'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        } elseif ($validated['data_type'] === 'JSON') {
            // Validate JSON format
            json_decode($validated['setting_value']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['setting_value' => 'Invalid JSON format']);
            }
        }

        SystemSetting::create($validated);

        return redirect()->route('admin.system-settings.index')
            ->with('success', 'System setting created successfully.');
    }

    /**
     * Remove the specified system setting from storage.
     */
    public function destroy(SystemSetting $systemSetting)
    {
        $systemSetting->delete();

        return redirect()->route('admin.system-settings.index')
            ->with('success', 'System setting deleted successfully.');
    }
}
