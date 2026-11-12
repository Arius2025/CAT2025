<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSoalHistory extends Model
{
    protected $table = 'user_soal_histories';

    protected $fillable = [
        'user_id',
        'soal_id',
        'kategori',
        'signature',
        'ujian_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}

