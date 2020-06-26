<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $guarded = [];

    public function stats()
    {
        return $this->hasMany('App\Stat','player_id');
    }
}
