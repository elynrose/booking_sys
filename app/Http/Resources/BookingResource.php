<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'schedule_id' => $this->schedule_id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            'schedule' => $this->whenLoaded('schedule', function () {
                return [
                    'id' => $this->schedule->id,
                    'title' => $this->schedule->title,
                    'start_time' => $this->schedule->start_time,
                    'end_time' => $this->schedule->end_time,
                    'max_participants' => $this->schedule->max_participants,
                    'current_participants' => $this->schedule->current_participants,
                    'trainer' => $this->schedule->trainer ? [
                        'id' => $this->schedule->trainer->id,
                        'name' => $this->schedule->trainer->user->name ?? 'Unknown',
                    ] : null,
                ];
            }),
            
            'children' => $this->whenLoaded('children', function () {
                return $this->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'age' => $child->age,
                    ];
                });
            }),
            
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'paid_at' => $payment->paid_at?->toISOString(),
                    ];
                });
            }),
            
            'checkins' => $this->whenLoaded('checkins', function () {
                return $this->checkins->map(function ($checkin) {
                    return [
                        'id' => $checkin->id,
                        'checkin_time' => $checkin->checkin_time?->toISOString(),
                        'checkout_time' => $checkin->checkout_time?->toISOString(),
                        'status' => $checkin->status,
                    ];
                });
            }),
        ];
    }
}
