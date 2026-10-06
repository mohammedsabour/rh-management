<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'date_absence',
        "justificatif",
        'type',
    ];

    public function employe()
    {
      return $this->belongsTo(User::class, 'employe_id');
    }
}
