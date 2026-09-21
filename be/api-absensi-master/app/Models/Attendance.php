<?php

namespace App\Models;

use App\Http\Helpers\MethodsHelpers;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    // protected $table = 'attendances';
    protected $fillable = ['userId', 'mediaAttendaceId', 'mediaOfWorkId', 'projectId', 'latitude', 'longtitude', 'date', 'time', 'type'];

    public function scopeFilterByField($query, $record, $value)
    {
        MethodsHelpers::filterByField($query, $record, $value);
    }

    public function scopeEntities($query, $entities)
    {
        MethodsHelpers::entities($query, $entities);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectId');
    }

    public function media()
    {
        return $this->belongsTo(Medias::class, 'mediaAttendaceId');
    }

    public function mediaProof()
    {
        return $this->belongsTo(Medias::class, 'mediaOfWorkId');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function scopeWhereDivision($query, $divisionIds)
    {
        if ($query && $divisionIds) {
            $divisionIds = array_filter($divisionIds, function ($value) {
                return $value !== null;
            });
            $query->whereHas('project.division', function (Builder $subQuery) use ($divisionIds) {
                $subQuery->whereIn('devisionId', $divisionIds);
            });
        }

        return $query;
    }

    public function scopeWhereOvertimeShift($query, $status)
    {
        if ($query && $status) {
            $query->whereHas('shift', function (Builder $subQuery) use ($status) {
                $subQuery->where('type', $status);
            });
        }

        return $query;
    }

    public function scopeFilterSummary($query, $summary, $request, $user_id)
    {
        if ($query && $summary && $user_id) {
            $basicQuery = $query->filterByField('projectId', $request->projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id);
            if ($summary !== 'overtime' && $summary !== 'all' && $summary !== 'late') {
                $query->filterByField('projectId', $request->projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)->filterByField('type', $summary);
                // dd($request->projectId);
            } else if ($summary === 'overtime') {
                $query->filterByField('projectId', $request->projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)->whereOvertimeShift('lembur');
            } else if ($summary === 'late') {
                $query->filterByField('projectId', $request->projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)->filterByField('status', $summary);
            }
        }

        return $query;
    }

    public function scopeWhereDateRange($query, $target_field, $since, $until)
    {
        MethodsHelpers::filterByDateRange($query, $target_field, $since, $until);
    }
}
