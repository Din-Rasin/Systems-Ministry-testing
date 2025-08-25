@extends('layouts.app')

@section('title', 'Workflow Visualization')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Workflow Visualization</h1>
            <p class="mt-1 text-gray-600">Track the progress of request #{{ $workflowRequest->id }} through the approval process</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Request Details -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Request Details</h2>

                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Title</p>
                                <p class="font-medium">{{ $workflowRequest->title ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Type</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($workflowRequest->type) }}
                                </span>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$workflowRequest->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($workflowRequest->status) }}
                                </span>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Submitted by</p>
                                <p class="font-medium">{{ $workflowRequest->user->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ $workflowRequest->user->email ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Submitted on</p>
                                <p class="font-medium">{{ $workflowRequest->created_at->format('M d, Y H:i') }}</p>
                            </div>

                            @if($workflowRequest->type === 'leave' && $workflowRequest->leaveRequest)
                                <div>
                                    <p class="text-sm text-gray-600">Leave Dates</p>
                                    <p class="font-medium">{{ $workflowRequest->leaveRequest->start_date->format('M d, Y') }} - {{ $workflowRequest->leaveRequest->end_date->format('M d, Y') }}</p>
                                </div>
                            @elseif($workflowRequest->type === 'mission' && $workflowRequest->missionRequest)
                                <div>
                                    <p class="text-sm text-gray-600">Mission Dates</p>
                                    <p class="font-medium">{{ $workflowRequest->missionRequest->start_date->format('M d, Y') }} - {{ $workflowRequest->missionRequest->end_date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Destination</p>
                                    <p class="font-medium">{{ $workflowRequest->missionRequest->destination ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Budget</p>
                                    <p class="font-medium">${{ number_format($workflowRequest->missionRequest->budget ?? 0, 2) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Workflow Visualization -->
                <div class="lg:col-span-2">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Approval Workflow</h2>

                        <div class="space-y-4">
                            @php
                                $sortedSteps = $workflowRequest->workflow->steps->sortBy('step_number');
                            @endphp

                            @foreach($sortedSteps as $step)
                                @php
                                    // Get approval status for this step
                                    $approval = $workflowRequest->approvals->firstWhere('step_number', $step->step_number);
                                    $status = 'upcoming';

                                    if ($approval) {
                                        $status = $approval->status;
                                    } elseif ($step->step_number < $workflowRequest->current_step_number) {
                                        $status = 'approved';
                                    } elseif ($step->step_number == $workflowRequest->current_step_number) {
                                        $status = 'pending';
                                    }

                                    // Get approver for this step
                                    $approver = null;
                                    if ($approval) {
                                        $approver = $approval->approver;
                                    } else {
                                        // Find a user with the required role
                                        $approver = $approvers->firstWhere('role', $step->role->name ?? '');
                                    }

                                    // Status colors and icons
                                    $statusConfig = [
                                        'approved' => ['color' => 'bg-green-100 text-green-800', 'icon' => '✓'],
                                        'rejected' => ['color' => 'bg-red-100 text-red-800', 'icon' => '✗'],
                                        'pending' => ['color' => 'bg-yellow-100 text-yellow-800', 'icon' => '⋯'],
                                        'upcoming' => ['color' => 'bg-gray-100 text-gray-800', 'icon' => '○'],
                                    ];
                                @endphp

                                <div class="border border-gray-200 rounded-lg bg-white">
                                    <div class="p-4 flex items-center">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center {{ $statusConfig[$status]['color'] }}">
                                            <span class="font-bold">{{ $statusConfig[$status]['icon'] }}</span>
                                        </div>

                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="font-medium text-gray-900">
                                                    Step {{ $step->step_number }}: {{ $step->role->name ?? 'Unknown Role' }}
                                                </h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig[$status]['color'] }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            </div>

                                            @if($approver)
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ $approver->name }} ({{ $approver->email }})
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    @if($approval)
                                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                                            <div class="text-sm">
                                                @if($approval->approved_at)
                                                    <p><span class="font-medium">Approved on:</span> {{ $approval->approved_at->format('M d, Y H:i') }}</p>
                                                @endif

                                                @if($approval->comments)
                                                    <p class="mt-1"><span class="font-medium">Comments:</span> {{ $approval->comments }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Summary -->
                        <div class="mt-6 bg-blue-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    Current Status:
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig[$workflowRequest->status]['color'] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($workflowRequest->status) }} at Step {{ $workflowRequest->current_step_number }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
