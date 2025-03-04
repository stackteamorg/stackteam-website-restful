<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopBar extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'button_name', 'link'];
}
