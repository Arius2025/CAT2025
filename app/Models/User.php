<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'instansi',
        'no_hp',
        'is_master_admin',
        'is_active',
        'preferred_exam_mode_id',
        'preferred_exam_mode_ids',
        'preferred_sub_category_id',
        'preferred_formasi_names',
        'visible_menu_keys',
        'theme',
        'activated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'password' => 'hashed',
        'is_master_admin' => 'boolean',
        'is_active' => 'boolean',
        'preferred_exam_mode_ids' => 'array',
        'preferred_formasi_names' => 'array',
        'visible_menu_keys' => 'array',
    ];

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function preferredExamMode()
    {
        return $this->belongsTo(ExamMode::class, 'preferred_exam_mode_id');
    }

    public function preferredSubCategory()
    {
        return $this->belongsTo(SubCategory::class, 'preferred_sub_category_id');
    }

    public function preferredExamModeIds(): array
    {
        $rows = is_array($this->preferred_exam_mode_ids ?? null) ? $this->preferred_exam_mode_ids : [];
        $rows = array_values(array_filter(array_map(fn ($id) => (int) $id, $rows)));
        if (!empty($rows)) {
            return array_values(array_unique($rows));
        }

        return !empty($this->preferred_exam_mode_id) ? [(int) $this->preferred_exam_mode_id] : [];
    }

    public function preferredFormasiNames(): array
    {
        $rows = is_array($this->preferred_formasi_names ?? null) ? $this->preferred_formasi_names : [];
        $rows = array_values(array_filter(array_map(fn ($name) => trim((string) $name), $rows)));
        if (!empty($rows)) {
            return array_values(array_unique($rows));
        }

        return !empty($this->preferredSubCategory?->name) ? [trim((string) $this->preferredSubCategory->name)] : [];
    }

    public function isMasterAdmin(): bool
    {
        if (Schema::hasColumn('users', 'is_master_admin')) {
            return (bool) $this->is_master_admin;
        }

        return (int) $this->id === 1;
    }

    public function isActive(): bool
    {
        if (Schema::hasColumn('users', 'is_active')) {
            return (bool) $this->is_active;
        }

        return true;
    }

    public function activationStatus(): string
    {
        if ($this->isActive()) {
            return 'active';
        }

        if (Schema::hasColumn('users', 'activated_at') && empty($this->activated_at)) {
            return 'pending';
        }

        return 'inactive';
    }

    public function activationStatusLabel(): string
    {
        return match ($this->activationStatus()) {
            'active' => 'Aktif',
            'pending' => 'Menunggu',
            default => 'Nonaktif',
        };
    }

    public function menuAllowed(string $key): bool
    {
        $visible = is_array($this->visible_menu_keys ?? null) ? $this->visible_menu_keys : [];
        if (empty($visible)) {
            return true;
        }

        if (in_array($key, $visible, true)) {
            return true;
        }

        $fallbackMap = [
            'admin_qc' => ['admin_audit', 'admin_generate', 'admin_bank'],
        ];

        if (isset($fallbackMap[$key])) {
            foreach ($fallbackMap[$key] as $legacyKey) {
                if (in_array($legacyKey, $visible, true)) {
                    return true;
                }
            }
        }

        if ($key === 'admin_qc' && $this->isMasterAdmin()) {
            return true;
        }

        return false;
    }
}
