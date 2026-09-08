<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Player extends Model {
    public $timestamps = false;
    protected $guarded = [];

    public function team() {
        return $this->belongsTo(Team::class);
    }
}
