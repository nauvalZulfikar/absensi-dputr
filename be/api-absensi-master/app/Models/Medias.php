<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medias extends Model
{
    use HasFactory;

    protected $fillable = ['url','type'];

    public function profile()
    {
        return $this->hasOne(Profile::class, 'mediaId');
    }
}
