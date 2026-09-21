<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftHaveProject extends Model
{
    use HasFactory;
    protected $table = 'shift_have_projects';

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
