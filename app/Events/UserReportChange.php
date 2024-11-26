<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserReportChange implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userReportId;
    public $institutionId;

    /**
     * Create a new event instance.
     */
    public function __construct($institutionId, $userReportId)
    {
        $this->institutionId = $institutionId;
        $this->userReportId = $userReportId;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'userReportChange';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("privileged-channel.$this->institutionId.$this->userReportId"),
            new PrivateChannel("privileged-channel.$this->institutionId"),
        ];
    }
}
