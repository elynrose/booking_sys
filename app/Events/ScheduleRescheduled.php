<?php

namespace App\Events;

use App\Models\Schedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleRescheduled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $schedule;
    public $oldDate;
    public $oldTime;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Schedule $schedule, $oldDate = null, $oldTime = null, $reason = null)
    {
        $this->schedule = $schedule;
        $this->oldDate = $oldDate;
        $this->oldTime = $oldTime;
        $this->reason = $reason;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
} 