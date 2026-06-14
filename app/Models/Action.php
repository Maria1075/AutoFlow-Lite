<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Action extends Model
{
    protected $fillable = [
        'workflow_id',
        'name',
        'type',
        'config',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            // config se almacena como JSON y se devuelve como array PHP
            'config' => 'array',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
