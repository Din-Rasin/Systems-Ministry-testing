<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Events\NotificationEvent;

class NotificationService
{
    /**
     * Send a notification to a user
     */
    public function sendNotification(User $user, string $type, array $data = []): Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'data' => $data,
            ]);

            // Here you would integrate with your preferred notification channel
            // For example, email, SMS, push notifications, etc.
            $this->sendEmailNotification($user, $type, $data);

            // Dispatch the notification event for real-time updates
            event(new NotificationEvent($notification));

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification(User $user, string $type, array $data = [])
    {
        // This would integrate with your email service
        // For now, we'll just log it
        Log::info("Email notification sent to {$user->email} for type: {$type}", $data);
    }

    /**
     * Send request submitted notification
     */
    public function sendRequestSubmittedNotification($request)
    {
        $this->sendNotification(
            $request->user,
            'request_submitted',
            [
                'request_id' => $request->id,
                'request_type' => $request->type,
                'message' => 'Your request has been submitted successfully.',
            ]
        );
    }

    /**
     * Send request approved notification
     */
    public function sendRequestApprovedNotification($request, $approver)
    {
        $this->sendNotification(
            $request->user,
            'request_approved',
            [
                'request_id' => $request->id,
                'request_type' => $request->type,
                'approved_by' => $approver->name,
                'message' => 'Your request has been approved.',
            ]
        );
    }

    /**
     * Send request rejected notification
     */
    public function sendRequestRejectedNotification($request, $approver, $comments = null)
    {
        $this->sendNotification(
            $request->user,
            'request_rejected',
            [
                'request_id' => $request->id,
                'request_type' => $request->type,
                'rejected_by' => $approver->name,
                'comments' => $comments,
                'message' => 'Your request has been rejected.',
            ]
        );
    }

    /**
     * Send pending approval notification
     */
    public function sendPendingApprovalNotification($approval)
    {
        $this->sendNotification(
            $approval->approver,
            'pending_approval',
            [
                'request_id' => $approval->request->id,
                'request_type' => $approval->request->type,
                'requester' => $approval->request->user->name,
                'message' => 'You have a pending approval request.',
            ]
        );
    }

    /**
     * Send workflow completed notification
     */
    public function sendWorkflowCompletedNotification($request)
    {
        $this->sendNotification(
            $request->user,
            'workflow_completed',
            [
                'request_id' => $request->id,
                'request_type' => $request->type,
                'status' => $request->status,
                'message' => 'Your request workflow has been completed.',
            ]
        );
    }
}
