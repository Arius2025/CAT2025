<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ExamMode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'color',
        'has_figural',
        'is_active',
    ];

    protected $casts = [
        'has_figural' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function packages()
    {
        return $this->hasMany(ExamModePackage::class)->orderBy('sort_order')->orderBy('code');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function defaultSkd(): ?self
    {
        if (!Schema::hasTable('exam_modes')) {
            return null;
        }
        return static::query()->where('code', 'skd')->first();
    }
}
