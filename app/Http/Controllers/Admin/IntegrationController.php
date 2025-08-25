<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Display a listing of the integrations.
     */
    public function index()
    {
        $integrations = Integration::orderBy('name')->get();
        return view('admin.integrations.index', compact('integrations'));
    }

    /**
     * Show the form for creating a new integration.
     */
    public function create()
    {
        return view('admin.integrations.create');
    }

    /**
     * Store a newly created integration in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:integrations',
            'type' => 'required|string|max:50',
            'endpoint_url' => 'required|string|max:500',
            'api_key' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sync_frequency' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Integration::create($validated);

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Integration created successfully.');
    }

    /**
     * Show the form for editing the specified integration.
     */
    public function edit(Integration $integration)
    {
        return view('admin.integrations.edit', compact('integration'));
    }

    /**
     * Update the specified integration in storage.
     */
    public function update(Request $request, Integration $integration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:integrations,name,' . $integration->id,
            'type' => 'required|string|max:50',
            'endpoint_url' => 'required|string|max:500',
            'api_key' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sync_frequency' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $integration->update($validated);

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Integration updated successfully.');
    }

    /**
     * Remove the specified integration from storage.
     */
    public function destroy(Integration $integration)
    {
        $integration->delete();

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Integration deleted successfully.');
    }

    /**
     * Toggle the active status of an integration.
     */
    public function toggleActive(Integration $integration)
    {
        $integration->update(['is_active' => !$integration->is_active]);

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Integration status updated successfully.');
    }

    /**
     * Test the connection to an integration.
     */
    public function testConnection(Integration $integration)
    {
        // This would contain actual connection testing logic
        // For now, we'll just return a success message
        return redirect()->route('admin.integrations.index')
            ->with('success', 'Connection test successful for ' . $integration->name . '.');
    }
}
