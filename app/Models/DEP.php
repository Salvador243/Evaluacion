<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DEP extends Model
{
    use HasFactory;
    protected $table = 'DEP';
    protected $fillable = [
        'CVE_DEP', 'STATUS', 'NOMBRE'
    ];
}
