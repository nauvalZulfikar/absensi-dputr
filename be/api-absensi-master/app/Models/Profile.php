<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'jabatan',
        'mediaId',
        'name',
        'phoneNumber',
        'gender',
        'address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
    
    public function medias()
    {
        return $this->belongsTo(Medias::class, 'mediaId');
    }
}
