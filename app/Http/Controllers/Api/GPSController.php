<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\GpsData;

class GPSController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return string
	 */
	public function index() {
//
	}

    public function create(Request $request)
    {
        $lat = $request->get("lat");
        $lon = $request->get("lon");
        $altitude = $request->get("altitude");
        $speed = $request->get("speed");
        $bearing = $request->get("bearing");
        $hdop = $request->get("hdop");
        $gpsd = new GpsData();
        $gpsd->lat = $lat;
        $gpsd->long = $lon;
        $gpsd->altitude = $altitude;
        $gpsd->speed = $speed;
        $gpsd->hdop = $hdop;
        $gpsd->save();
    }

}
