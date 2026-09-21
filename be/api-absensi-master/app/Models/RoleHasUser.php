<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleHasUser extends Model
{
    use HasFactory;

    protected $table = 'role_has_users';

    protected $fillable = [
        'userId',
        'roleId',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }
}
