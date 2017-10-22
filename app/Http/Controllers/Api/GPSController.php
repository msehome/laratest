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
        $gpsd->hdop = "HI";
        $gpsd->save();
        GpsData::create($request->all());
    }

}
