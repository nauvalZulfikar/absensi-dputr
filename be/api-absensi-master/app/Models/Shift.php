<?php

namespace App\Models;

use App\Http\Helpers\MethodsHelpers;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;
    protected $table = 'shifts';
    protected $fillable = [
        'timeIn', 'timeOut'
    ];

    public function scopeFilterByField($query, $record, $value)
    {
        MethodsHelpers::filterByField($query, $record, $value);
    }

    public function scopeEntities($query, $entities)
    {
        MethodsHelpers::entities($query, $entities);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectId');
    }
    public function userShift()
    {
        return $this->hasMany(ShiftHaveUser::class, 'shift_id');
    }

    public function projectShift()
    {
        return $this->hasMany(ShiftHaveProject::class, 'shift_id');
    }

    public function scopeFilterMyShift($query, $userId)
    {
        if ($query && $userId && $userId !== null) {
            $query->whereHas('userShift.user', function (Builder $subQuery) use ($userId) {
                $subQuery->where('id', $userId);
            });
        }

        return $query;
    }

    public function scopeFilterByProjectId($query, $projectId)
    {
        if ($query && $projectId) {
            $query->whereHas('projectShift', function (Builder $subQuery) use ($projectId) {
                $subQuery->where('project_id', $projectId);
            });
        }

        return $query;
    }
}
