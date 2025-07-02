<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $changedFields;
    public $oldData;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, array $changedFields = [], array $oldData = [])
    {
        $this->user = $user;
        $this->changedFields = $changedFields;
        $this->oldData = $oldData;
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