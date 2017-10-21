<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class GpsData extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'gps_data';

    protected $fillable = [
        'uid', 'lat', 'lon','altitude','speed','bearing','hdop'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

}
