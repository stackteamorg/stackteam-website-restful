<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function getRouteKeyName()
    {
        return 'slug'; // Use 'slug' for URLs
    }

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt',
        'status', 'user_id', 'views', 'published_at',
        'image', 'category_id', 'post_type'  // New columns added!
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
