<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug'; // Default is still slug for URLs
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Check if $value is numeric (an ID) or a string (a slug)
        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }

        // Otherwise, use the default slug lookup
        return $this->where('slug', $value)->first();
    }

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt',
        'status', 'user_id', 'views', 'published_at',
        'image', 'category_id', 'post_type'
    ];

    // Rest of your model remains unchanged
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
    protected $hidden = ['user', 'user_id', 'category'];

    public function openGraph()
    {
        return $this->hasOne(OpenGraph::class);
    }
}
