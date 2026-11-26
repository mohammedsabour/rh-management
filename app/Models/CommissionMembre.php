<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionMembre extends Model
{
    protected $table = 'commission_membres';

    public const ROLES = [
        'president' => 'Président',
        'membre' => 'Membre',
    ];

    protected $fillable = ['commission_id', 'user_id', 'role'];

    public function commission(): BelongsTo
    {
        return $this->belongsTo(CommissionInventaire::class, 'commission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
