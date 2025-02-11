<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function getRouteKeyName()
    {
        return 'slug'; // Use 'slug' instead of 'id' for URLs
    }

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt',
        'status', 'user_id', 'views', 'published_at',
        'image' // Added image field
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
