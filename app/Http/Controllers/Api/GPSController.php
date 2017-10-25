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
        $gpsd = new GpsData();
        $gpsd->hdop = "TEST";
        $gpsd->save();
        $uid = $request->get("uid");
        $lat = $request->get("lat");
        $lon = $request->get("lon");
        $altitude = $request->get("altitude");
        $speed = $request->get("speed");
        $bearing = $request->get("bearing");
        $hdop = $request->get("hdop");
        $gpsd = new GpsData();
        $gpsd->uid = $uid;
        $gpsd->lat = $lat;
        $gpsd->lon = $lon;
        $gpsd->altitude = $altitude;
        $gpsd->speed = $speed;
        $gpsd->bearing = $bearing;
        $gpsd->hdop = $hdop;
        $gpsd->save();
    }

}
