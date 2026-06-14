<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'process_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** El proceso al que está vinculado este workflow (opcional) */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /** Un workflow tiene uno o varios triggers que lo activan */
    public function triggers(): HasMany
    {
        return $this->hasMany(Trigger::class);
    }

    /** Un workflow tiene una o varias acciones que ejecuta */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class)->orderBy('sort_order');
    }

    /** Historial de ejecuciones de este workflow */
    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class)->latest();
    }
}
