<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeathCertificateForm extends Model
{
    use HasFactory;

    public function healthUnit()
    {
        return $this->belongsTo(HealthUnit::class, 'cnes_code', 'cnes_code');
    }
}
