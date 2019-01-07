<?php

namespace Swamsid\Keuangan\Constructor\Func\Jurnal;
use DB;
use keuangan;

class idle{

	public function addJurnal(Array $detail, String $tanggalTransaksi, String $nomorTransaksi, String $keteranganTransaksi, String $typeTransaksi, String $comp){

		$det = []; $num = 1;
		$id = (DB::table('dk_jurnal')->max('jr_id')) ? (DB::table('dk_jurnal')->max('jr_id') + 1) : 1;

		DB::table('dk_jurnal')->insert([
			'jr_id'				=> $id,
			'jr_type'			=> $typeTransaksi,
			'jr_comp'			=> $comp,
			'jr_ref'			=> $nomorTransaksi,
			'jr_tanggal_trans'	=> $tanggalTransaksi,
			'jr_keterangan'		=> $keteranganTransaksi,
		]);

		foreach($detail as $key => $akun){
			$det[$key]	= [
				'jrdt_jurnal'		=> $id,
				'jrdt_nomor'		=> $num,
				'jrdt_akun'			=> $akun['jrdt_akun'],
				'jrdt_value'		=> $akun['jrdt_value'],
				'jrdt_dk'			=> $akun['jrdt_dk']
			];

			$num++;
		}

		DB::table('dk_jurnal_detail')->insert($det);

		keuangan::akunSaldo()->increaseSaldo($detail, $tanggalTransaksi, $typeTransaksi);
	}

	public function dropJurnal(String $idJurnal){
		$data = DB::table('dk_jurnal')->where('jr_id', $idJurnal);

		if($data->first()){
			keuangan::akunSaldo()->decrease($idJurnal, $data->first()->jr_type, $data->first()->jr_tanggal_trans);

			$data->delete();
		}

		// return json_encode('Tidak Ada');
	}

}