<?php

namespace App\Models;

use App\Http\Helpers\MethodsHelpers;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\RoleHasUser;
use Illuminate\Contracts\Database\Eloquent\Builder;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'userName',
        'email',
        'email_verified_at',
        'password',
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
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scopeEntities($query, $entities)
    {
        MethodsHelpers::entities($query, $entities);
    }

    public static function sendMail($email, $mailData)
    {
        MethodsHelpers::sendMail($email, $mailData);
    }

    public function roles()
    {
        return $this->hasMany(RoleHasUser::class, 'userId');
    }

    public function projects()
    {
        return $this->hasMany(UserHaveProject::class, 'user_id');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'userId');
    }

    public function shift()
    {
        return $this->hasMany(ShiftHaveUser::class, 'user_id');
    }

    public function divisions()
    {
        return $this->hasMany(UserHaveDivision::class, 'user_id');
    }

    /**
     * Full admin (admin/superadmin) — akses lintas divisi tak dibatasi.
     * CATATAN: user_admin (Pengawas) TIDAK termasuk di sini; dia hanya boleh
     * divisinya sendiri (lihat canManageDivision()).
     */
    public function isFullAdmin(): bool
    {
        return $this->roles()
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['admin', 'superadmin']))
            ->exists();
    }

    /**
     * Boleh menulis (buat/ubah/hapus) resource di divisi ini?
     * - Full admin: selalu boleh.
     * - user_admin: hanya divisi yang ditugaskan padanya (user_have_division).
     * - $divisionId null → tolak, kecuali full admin.
     */
    public function canManageDivision($divisionId): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }
        if ($divisionId === null) {
            return false;
        }
        return $this->divisions()->where('devision_id', $divisionId)->exists();
    }

    /**
     * Boleh menulis resource yang menempel pada sebuah project?
     * Project → divisi (projects.devisionId) → canManageDivision.
     * Project tak ada → tolak (kecuali full admin).
     */
    public function canManageProject($projectId): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }
        $divisionId = \App\Models\Project::whereKey($projectId)->value('devisionId');
        return $this->canManageDivision($divisionId);
    }

    /**
     * Boleh menulis kalau SEMUA project ini ada di divisinya (konservatif —
     * satu project asing pun langsung tolak). Daftar kosong → tolak (kecuali
     * full admin), supaya operasi tanpa scope jelas tak lolos.
     */
    public function canManageProjects(array $projectIds): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }
        if (empty($projectIds)) {
            return false;
        }
        foreach ($projectIds as $pid) {
            if (! $this->canManageProject($pid)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Boleh menulis sebuah shift? Shift → project(s) via shift_have_projects →
     * divisi. Harus menguasai SEMUA project yang menempel pada shift itu.
     */
    public function canManageShift($shiftId): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }
        $projectIds = \App\Models\ShiftHaveProject::where('shift_id', $shiftId)
            ->pluck('project_id')->all();
        return $this->canManageProjects($projectIds);
    }

    public function scopeWhereDivisions($query, $divisionIds)
    {
        if ($query && $divisionIds) {
            $divisionIds = array_filter($divisionIds, function ($value) {
                return $value !== null;
            });
            $query->whereHas('divisions', function ($q) use ($divisionIds) {
                $q->whereIn('devision_id', $divisionIds);
            });
        }

        return $query;
    }

    public function scopeWhereProjects($query, $projectIds)
    {

        if ($query && $projectIds) {
            $projectIds = array_filter($projectIds, function ($value) {
                return $value !== null;
            });
            $query->whereHas('projects', function ($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }

        return $query;
    }

    public function scopeWhereHasNotDivisions($query, $divisionIds)
    {
        // Memeriksa apakah $projectIds tidak kosong dan $query terdefinisi
        if ($divisionIds && $query) {
            $projectIds = array_filter($divisionIds, function ($value) {
                return $value !== null;
            });
            // Mengevaluasi array kosong menggunakan empty
            $query->whereDoesntHave('divisions', function ($q) use ($divisionIds) {
                $q->whereIn('devision_id', $divisionIds);
            });
        }

        return $query;
    }

    public function scopeWhereHasNotProject($query, $projectIds)
    {
        if ($projectIds && $query) {
            $projectIds = array_filter($projectIds, function ($value) {
                return $value !== null;
            });

            $query->whereDoesntHave('projects', function ($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }

        return $query;
    }


    public function scopeWhereInArray($query, $record, $values)
    {
        MethodsHelpers::whereInArray($query, $record, $values);
    }

    public function scopeWhereRoles($query, $roleIds)
    {
        if ($query && $roleIds) {
            $query->whereHas('roles', function ($q) use ($roleIds) {
                $q->where('roleId', '!=', 1)->whereIn('roleId', $roleIds);
            });
        }

        return $query;
    }

    public function scopeFilterSummary($query, $summary)
    {
        if ($query && $summary) {
            if ($summary && $summary !== 'all') {
                if ($summary === 'active' || $summary === 'not_active') {
                    $query->where('status', $summary);
                } else {
                    $roles = ['superadmin', 'user', 'admin', 'supervisor'];
                    $index = array_search($summary, $roles);

                    $query->whereHas('roles', function (Builder $query) use ($index) {
                        if (isset($index)) {
                            $query->where('roleId', $index + 1);
                        }
                    });
                }
            }

            return $query;
        }
    }

    public function scopeFilterByShift($query, $shiftId)
    {
        if ($query && $shiftId) {
            $query->whereHas('shift', function (Builder $subQuery) use ($shiftId) {
                $subQuery->where('shift_id', $shiftId);
            });
        }

        return $query;
    }

    public function scopeWhereHasNotShift($query, $shiftIds)
    {
        if ($shiftIds && $query) {
            $shiftIds = array_filter($shiftIds, function ($value) {
                return $value !== null;
            });

            $query->whereDoesntHave('shift', function ($q) use ($shiftIds) {
                $q->whereIn('id', $shiftIds);
            });
        }

        return $query;
    }
}
