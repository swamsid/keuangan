<?php

namespace App\Http\Controllers\Keuangan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Keuangan\periode_keuangan as periode;

class periode_keuangan_controller extends Controller {

	public function index(){
		$data = periode::get();

		return view('keuangan.periode_keuangan.index', compact('data'));
	}

}