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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['category_name', 'category_slug', 'user_name', 'open_graph_data'];

    public function getOpenGraphDataAttribute()
    {
        return $this->openGraph ? $this->openGraph->toArray() : null;
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : null;
    }

    public function getCategorySlugAttribute()
    {
        return $this->category ? $this->category->slug : null;
    }

    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : null;
    }

    // Hide unnecessary fields
    protected $hidden = ['user', 'category_id', 'user_id', 'category'];

    public function openGraph()
    {
        return $this->hasOne(OpenGraph::class);
    }

}
