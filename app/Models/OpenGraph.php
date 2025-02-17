<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenGraph extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'og_title', 'og_description', 'og_image'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
