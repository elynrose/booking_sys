<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkScheduleDuplicationReport extends Notification
{
    use Queueable;

    public $reportData;

    /**
     * Create a new notification instance.
     */
    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->reportData;
        
        $targetMonthsText = is_array($data['target_months']) ? implode(', ', $data['target_months']) : $data['target_months'];
        
        $mail = (new MailMessage)
            ->subject('Bulk Schedule Duplication Report - ' . $data['selected_schedules'] . ' schedules to ' . $targetMonthsText)
            ->greeting('Hello!')
            ->line('The bulk schedule duplication process has been completed.')
            ->line('Here is a summary of the operation:')
            ->line('📸 **Note:** Schedule photos have been automatically copied to the new schedules.');

        // Add summary
        $mail->line('📊 **Summary:**');
        $mail->line('- Total schedules processed: ' . $data['total_processed']);
        $mail->line('- Successfully created: ' . $data['created_count'] . ' schedules');
        $mail->line('- Skipped (trainer unavailable): ' . $data['skipped_count'] . ' schedules');
        $mail->line('- Failed: ' . $data['failed_count'] . ' schedules');

        if (!empty($data['created_schedules'])) {
            $mail->line('✅ **Successfully Created Schedules:**');
            foreach ($data['created_schedules'] as $schedule) {
                $editUrl = url('/admin/schedules/' . $schedule['id'] . '/edit');
                $mail->line('- ' . $schedule['title'] . ' (' . $schedule['date'] . ' at ' . $schedule['time'] . ')');
                $mail->line('  📝 [Edit Schedule](' . $editUrl . ')');
            }
        }

        if (!empty($data['skipped_schedules'])) {
            $mail->line('⚠️ **Skipped Schedules (Trainer Unavailable):**');
            foreach ($data['skipped_schedules'] as $schedule) {
                $mail->line('- ' . $schedule['title'] . ' (' . $schedule['date'] . ' at ' . $schedule['time'] . ') - Trainer: ' . $schedule['trainer']);
            }
        }

        if (!empty($data['failed_schedules'])) {
            $mail->line('❌ **Failed Schedules:**');
            foreach ($data['failed_schedules'] as $schedule) {
                $mail->line('- ' . $schedule['title'] . ' - Error: ' . $schedule['error']);
            }
        }

        $mail->line('')
            ->line('The process was completed on ' . now()->format('F j, Y \a\t g:i A'))
            ->action('Add a trainer', url('/admin/schedules'))
            ->line('Thank you for using the Gym Management System!');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bulk_schedule_duplication_report',
            'data' => $this->reportData,
        ];
    }
} 