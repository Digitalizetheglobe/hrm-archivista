<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveNotification extends Notification
{
    use Queueable;

    protected $leave;
    protected $action;

    /**
     * Create a new notification instance.
     *
     * @param mixed $leave
     * @param string $action
     */
    public function __construct($leave, $action)
    {
        $this->leave = $leave;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $employee = $this->leave->employees;
        $employeeName = $employee ? $employee->name : 'An employee';
        $leaveType = $this->leave->leaveType;
        $leaveTypeTitle = $leaveType ? $leaveType->title : 'Leave';

        if ($this->action === 'created') {
            $message = "{$employeeName} has requested {$this->leave->total_leave_days} days of {$leaveTypeTitle} leave.";
        } else {
            $message = "Your {$leaveTypeTitle} leave request (from {$this->leave->start_date} to {$this->leave->end_date}) has been {$this->action}.";
        }

        return [
            'leave_id' => $this->leave->id,
            'employee_name' => $employeeName,
            'leave_type' => $leaveTypeTitle,
            'start_date' => $this->leave->start_date,
            'end_date' => $this->leave->end_date,
            'total_leave_days' => $this->leave->total_leave_days,
            'status' => $this->leave->status,
            'action' => $this->action,
            'message' => $message,
        ];
    }
}
