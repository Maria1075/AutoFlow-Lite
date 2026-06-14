<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'description'      => $this->description,
            'frequency'        => $this->frequency,
            'status'           => $this->status,
            'webhook_url'      => $this->webhook_url,
            'executions_count' => $this->executions_count,
            'success_count'    => $this->success_count,
            'success_rate'     => $this->success_rate,
            'is_automatizable' => $this->isAutomatizable(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
