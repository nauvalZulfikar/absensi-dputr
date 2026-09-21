<?php

namespace App\Models;

use App\Http\Helpers\MethodsHelpers;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Devision extends Model
{
    use HasFactory;

    protected $table = 'devisions';
    protected $fillable = [
        'name',
    ];

    // === Scopre === //

    public function scopeFilterByDateRange($query, $targetField, $since, $until)
    {
        MethodsHelpers::filterByDateRange($query, $targetField, $since, $until);
    }

    public function scopeFilterByField($query, $record, $target)
    {
        MethodsHelpers::filterByField($query, $record, $target);
    }

    public function scopeEntities($query, $entities)
    {
        MethodsHelpers::entities($query, $entities);
    }

    public function scopeWhereDivisions($query, $divisions)
    {
        MethodsHelpers::whereInArray($query, 'id', $divisions);
    }

    public function scopeExecuteType($query, $type, $paginate = null)
    {
        if ($query && $type) {
            if ($type === 'selected') {
                return $query->get();
            }
        }

        return $query->paginate($paginate);
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

    public function users()
    {
        return $this->hasMany(UserHaveDivision::class, 'devision_id');
    }

    public function scopeFilterMyDivisions($query, $userId)
    {
        if ($query && $userId && $userId !== null) {
            $query->whereHas('users', function (Builder $subQuery) use ($userId) {
                $subQuery->where('user_id', $userId);
            });
        }
    }
}
