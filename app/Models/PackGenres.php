<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackGenres extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'film_id',
        'genre_id'
    ];

}
