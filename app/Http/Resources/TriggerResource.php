<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TriggerResource extends JsonResource
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
            'workflow_id' => $this->workflow_id,
            'name' => $this->name,
            'type' => $this->type,
            'cron_expression' => $this->cron_expression,
            // URL pública para activar el workflow con un POST (solo si type=webhook)
            'webhook_url' => $this->webhook_url,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
