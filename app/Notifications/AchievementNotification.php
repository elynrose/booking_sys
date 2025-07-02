<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Child;
use Illuminate\Support\Facades\Route;

class AchievementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $child;
    protected $achievement;
    protected $milestone;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Child $child = null, $achievement = null, $milestone = null)
    {
        $this->user = $user;
        $this->child = $child;
        $this->achievement = $achievement;
        $this->milestone = $milestone;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $childName = $this->child ? $this->child->name : 'your child';
        
        $message = (new MailMessage)
            ->subject('Achievement Unlocked! 🎉')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('We\'re excited to celebrate an achievement with you!');

        if ($this->achievement) {
            $message->line('**Achievement:** ' . $this->achievement);
        }

        if ($this->milestone) {
            $message->line('**Milestone:** ' . $this->milestone);
        }

        $message->line('**Child:** ' . $childName)
               ->line('Keep up the amazing work! Every session brings new progress and achievements.')
               ->action('View Progress', route('frontend.home'))
               ->line('Thank you for being part of our gym family!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        $data = [
            'user_id' => $this->user->id,
            'child_id' => $this->child ? $this->child->id : null,
            'child_name' => $this->child ? $this->child->name : null,
            'achievement' => $this->achievement,
            'milestone' => $this->milestone,
            'message' => 'Achievement unlocked: ' . ($this->achievement ?: $this->milestone),
        ];
        if (Route::has('home')) {
            $data['action_url'] = route('home');
        }
        return $data;
    }
} 