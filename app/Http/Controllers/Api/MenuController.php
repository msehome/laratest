<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Menu;

class MenuController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return string
	 */
	public function index(Request $request) {
        $menu = Menu::where('parent_id',$request->get("level"))->get()->toJson(JSON_UNESCAPED_UNICODE);
        return $menu;
	}



}
