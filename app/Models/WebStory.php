<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebStory extends Model { 
    public $timestamps = true; 
    protected $guarded = []; 
    protected $casts = [
        'slides' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
