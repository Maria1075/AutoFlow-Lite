<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'frequency',
        'status',
        'script_path',
        'webhook_url',
        'executions_count',
        'success_count',
    ];

    /** @return HasMany<Workflow> */
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }

    // Calcular tasa de éxito
    public function getSuccessRateAttribute(): float
    {
        // Cast explícito para evitar division by zero cuando la BD devuelve string "0"
        if ((int) $this->executions_count === 0) {
            return 0;
        }

        return round(($this->success_count / $this->executions_count) * 100, 2);
    }

    // Determinar si es automatizable
    public function isAutomatizable(): bool
    {
        $automatizableKeywords = ['repetitivo', 'diario', 'informe', 'email', 'copia', 'backup', 'reporte'];
        $description = strtolower($this->description);

        foreach ($automatizableKeywords as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
