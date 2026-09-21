<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHaveDivision extends Model
{
    use HasFactory;
    protected $table = 'user_have_division';

    public function division()
    {
        return $this->belongsTo(Devision::class, 'devision_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
