<?php

namespace App\Models;

use App\Http\Helpers\MethodsHelpers;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Static_;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'devisionId',
        'userId',
        'projectNo',
        'startdate',
        'targetdate',
        'cost',
        'status',
        'rowStatus',
        'address',
        'latitude',
        'longtitude'
    ];

    public function scopeFilterByField($query, $record, $value)
    {
        MethodsHelpers::filterByField($query, $record, $value);
    }

    public function scopeEntities($query, $entities)
    {
        MethodsHelpers::entities($query, $entities);
    }

    public function scopeWhereWithEntities($query, $entities, $field, $target)
    {
        MethodsHelpers::whereWithEntities($query, $entities, $field, $target);
    }

    public function users()
    {
        return $this->hasMany(UserHaveProject::class, 'project_id');
    }

    public function shift()
    {
        return $this->hasMany(ShiftHaveProject::class, 'project_id');
    }

    public function division()
    {
        return $this->belongsTo(Devision::class, 'devisionId');
    }

    public function scopeWhereDivisions($query, $divisions)
    {
        MethodsHelpers::whereInArray($query, 'devisionId', $divisions);
    }

    public function scopeFilterMyProject($query, $userId)
    {
        if ($query && $userId && $userId !== null) {
            $query->whereHas('users', function (Builder $subQuery) use ($userId) {
                $subQuery->where('user_id', $userId);
            });
        }

        return $query;
    }

    public function scopeGenerateSlug($q, $title)
    {
        $new_slug = Str::slug($title);
        $slug_check = $q->where('slug', $new_slug)->count();
        if ($slug_check == 0) {
            $slug = $new_slug;
        } else {
            $check = 0;
            $unique = false;
            while ($unique == false) {
                $inc_id = ++$check;
                $check = $q->where('slug', $new_slug . '-' . $inc_id)->count();
                if ($check > 0) {
                    $unique = false;
                } else {
                    $unique = true;
                }
            }
            $slug = $new_slug . '-' . $inc_id;
        }

        return $slug;
    }
}
