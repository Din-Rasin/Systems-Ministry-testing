<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of the notification templates.
     */
    public function index()
    {
        $templates = NotificationTemplate::orderBy('template_type')->orderBy('template_name')->get();
        return view('admin.notification-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new notification template.
     */
    public function create()
    {
        return view('admin.notification-templates.create');
    }

    /**
     * Store a newly created notification template in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255|unique:notification_templates',
            'template_type' => 'required|string|max:50',
            'subject_template' => 'required|string|max:255',
            'message_template' => 'required|string',
            'placeholders' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Convert placeholders string to array
        if (!empty($validated['placeholders'])) {
            $validated['placeholders'] = array_map('trim', explode(',', $validated['placeholders']));
        } else {
            $validated['placeholders'] = [];
        }

        $validated['is_active'] = $request->has('is_active');

        NotificationTemplate::create($validated);

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Notification template created successfully.');
    }

    /**
     * Show the form for editing the specified notification template.
     */
    public function edit(NotificationTemplate $notificationTemplate)
    {
        // Convert placeholders array to comma-separated string for the form
        $placeholdersString = '';
        if (!empty($notificationTemplate->placeholders) && is_array($notificationTemplate->placeholders)) {
            $placeholdersString = implode(', ', $notificationTemplate->placeholders);
        }

        return view('admin.notification-templates.edit', compact('notificationTemplate', 'placeholdersString'));
    }

    /**
     * Update the specified notification template in storage.
     */
    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255|unique:notification_templates,template_name,' . $notificationTemplate->id,
            'template_type' => 'required|string|max:50',
            'subject_template' => 'required|string|max:255',
            'message_template' => 'required|string',
            'placeholders' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Convert placeholders string to array and then back to JSON
        if (!empty($validated['placeholders'])) {
            $placeholdersArray = array_map('trim', explode(',', $validated['placeholders']));
            $validated['placeholders'] = json_encode($placeholdersArray);
        } else {
            $validated['placeholders'] = json_encode([]);
        }

        $validated['is_active'] = $request->has('is_active');

        $notificationTemplate->update($validated);

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Notification template updated successfully.');
    }

    /**
     * Remove the specified notification template from storage.
     */
    public function destroy(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->delete();

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Notification template deleted successfully.');
    }

    /**
     * Toggle the active status of a notification template.
     */
    public function toggleActive(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->update(['is_active' => !$notificationTemplate->is_active]);

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Notification template status updated successfully.');
    }
}
