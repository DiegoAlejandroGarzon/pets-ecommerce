<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'species', 'breed', 'birth_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
