<?php

namespace Swamsid\Keuangan\Constructor\Func\Periode;
use DB;

class idle{

	public function generate(String $date){
		return $date;
	}

	public function emptyData(){
		$data = DB::table('dk_periode_keuangan')->where('pk_comp', modulSetting()['onLogin'])->first();

		if(!$data)
			return true;

		return false;
	}

	public function missing(){
		$tanggal = date('Y-m').'-01';
		$data = DB::table('dk_periode_keuangan')->where('pk_periode', $tanggal)->where('pk_comp', modulSetting()['onLogin'])->first();

		if(!$data)
			return true;

		return false;
	}

}