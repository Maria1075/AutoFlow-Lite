<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Trigger extends Model
{
    protected $fillable = [
        'workflow_id',
        'name',
        'type',
        'cron_expression',
        'webhook_token',
    ];

    /** Genera un token único cuando se crea un trigger de tipo webhook */
    protected static function booted(): void
    {
        static::creating(function (Trigger $trigger) {
            if ($trigger->type === 'webhook' && empty($trigger->webhook_token)) {
                $trigger->webhook_token = Str::random(40);
            }
        });
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /** URL pública para activar el workflow desde sistemas externos */
    public function getWebhookUrlAttribute(): ?string
    {
        if ($this->type !== 'webhook' || ! $this->webhook_token) {
            return null;
        }

        return url("/api/v1/webhooks/{$this->webhook_token}");
    }
}
