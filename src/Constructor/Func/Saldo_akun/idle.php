<?php

namespace Swamsid\Keuangan\Constructor\Func\Saldo_akun;
use DB;

class idle{

	public function addAkun(String $akun){

		$akun = DB::table('dk_akun')->where('ak_id', $akun)->first();
		$cek = DB::table('dk_periode_keuangan')->orderBy('pk_periode', 'asc')->get();
		$feeder	= [];

		foreach($cek as $key => $periode){
			$feeder[$key] = [
				"as_akun"					=> $akun->ak_id,
				"as_periode"				=> $periode->pk_periode,
				"as_saldo_awal"				=> $akun->ak_opening,
				"as_mut_kas_debet"			=> 0,
				"as_mut_kas_kredit"			=> 0,
				"as_mut_bank_debet"			=> 0,
				"as_mut_bank_kredit"		=> 0,
				"as_mut_memorial_debet"		=> 0,
				"as_mut_memorial_kredit"	=> 0,
				"as_saldo_akhir"			=> $akun->ak_opening
			];
		}

		DB::table('dk_akun_saldo')->insert($feeder);
	}

	public function increaseSaldo(Array $detail, String $tanggalTransaksi, String $typeTransaksi){
		$periode = date('Y-m', strtotime($tanggalTransaksi)).'-01';
		$state = substr($typeTransaksi, 0, 1);
		$det = [];

		// return json_encode($detail);

		foreach($detail as $key => $akun){
			
			$cek = DB::table('dk_akun')->where('ak_id', $akun['jrdt_akun'])->first();
			$equivalent = $saldoAwal = 0;

			switch ($state) {
				case 'K':
					DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun['jrdt_akun'])->update([
						'as_mut_kas_debet' => ($akun['jrdt_dk'] == 'D') ? DB::raw('as_mut_kas_debet + '.$akun['jrdt_value']) : DB::raw('as_mut_kas_debet'),

						'as_mut_kas_kredit' => ($akun['jrdt_dk'] == 'K') ? DB::raw('as_mut_kas_kredit + '.$akun['jrdt_value']) : DB::raw('as_mut_kas_kredit')
					]);
					break;

				case 'B':
					DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun['jrdt_akun'])->update([
						'as_mut_bank_debet' => ($akun['jrdt_dk'] == 'D') ? DB::raw('as_mut_bank_debet + '.$akun['jrdt_value']) : DB::raw('as_mut_bank_debet'),

						'as_mut_bank_kredit' => ($akun['jrdt_dk'] == 'K') ? DB::raw('as_mut_bank_kredit + '.$akun['jrdt_value']) : DB::raw('as_mut_bank_kredit')
					]);
					break;
				
				case 'M':
					DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun['jrdt_akun'])->update([
						'as_mut_memorial_debet' => ($akun['jrdt_dk'] == 'D') ? DB::raw('as_mut_memorial_debet + '.$akun['jrdt_value']) : DB::raw('as_mut_memorial_debet'),

						'as_mut_memorial_kredit' => ($akun['jrdt_dk'] == 'K') ? DB::raw('as_mut_memorial_kredit + '.$akun['jrdt_value']) : DB::raw('as_mut_memorial_kredit')
					]);
					break;
			}

			// $data = DB::table('dk_akun_saldo')
			// 			->where('as_periode', $periode)
			// 			->where('as_akun', $akun['jrdt_akun'])
			// 			->select(
			// 				DB::raw('coalesce(as_saldo_awal, 0) as as_saldo_awal'),
			// 				DB::raw('coalesce(as_mut_kas_debet, 0) as as_mut_kas_debet'),
			// 				DB::raw('coalesce(as_mut_kas_kredit, 0) as as_mut_kas_kredit'),
			// 				DB::raw('coalesce(as_mut_bank_debet, 0) as as_mut_bank_debet'),
			// 				DB::raw('coalesce(as_mut_bank_kredit, 0) as as_mut_bank_kredit'),
			// 				DB::raw('coalesce(as_mut_memorial_debet, 0) as as_mut_memorial_debet'),
			// 				DB::raw('coalesce(as_mut_memorial_kredit, 0) as as_mut_memorial_kredit')
			// 			)->first();

			if($cek->ak_posisi == 'D'){
					$equivalent = ($akun['jrdt_dk'] == 'K') ? ($akun['jrdt_value'] * -1) : $akun['jrdt_value'];
			}else{
					$equivalent = ($akun['jrdt_dk'] == 'D') ? ($akun['jrdt_value'] * -1) : $akun['jrdt_value'];
			}

			DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun['jrdt_akun'])->update([
				"as_saldo_akhir" => DB::raw('as_saldo_akhir + '.$equivalent)
			]);

			DB::table('dk_akun_saldo')->where('as_periode', '>', $periode)->where('as_akun', $akun['jrdt_akun'])->update([
				"as_saldo_awal"	 => DB::raw('as_saldo_awal + '.$equivalent),
				"as_saldo_akhir" => DB::raw('as_saldo_akhir + '.$equivalent)
			]);
		}
	}

	public function decrease(String $idJurnal, String $typeTransaksi, String $tanggalTransaksi){

		$periode = date('Y-m', strtotime($tanggalTransaksi)).'-01';
		$state = substr($typeTransaksi, 0, 1);
		$data = DB::table('dk_jurnal_detail')->where('jrdt_jurnal', $idJurnal)->get();

		foreach ($data as $key => $akun) {
			$cek = DB::table('dk_akun')->where('ak_id', $akun->jrdt_akun)->first();
			$equivalent = $saldoAwal = 0;

			switch ($state) {
				case 'K':
					DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun->jrdt_akun)->update([
						'as_mut_kas_debet' => ($akun->jrdt_dk == 'D') ? DB::raw('as_mut_kas_debet - '.$akun->jrdt_value) : DB::raw('as_mut_kas_debet'),

						'as_mut_kas_kredit' => ($akun->jrdt_dk == 'K') ? DB::raw('as_mut_kas_kredit - '.$akun->jrdt_value) : DB::raw('as_mut_kas_kredit')
					]);
					break;

				case 'B':
					DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun->jrdt_akun)->update([
						'as_mut_bank_debet' => ($akun->jrdt_dk == 'D') ? DB::raw('as_mut_bank_debet - '.$akun->jrdt_value) : DB::raw('as_mut_bank_debet'),

						'as_mut_bank_kredit' => ($akun->jrdt_dk == 'K') ? DB::raw('as_mut_bank_kredit - '.$akun->jrdt_value) : DB::raw('as_mut_bank_kredit')
					]);
					break;
				
				case 'M':
					DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun->jrdt_akun)->update([
						'as_mut_memorial_debet' => ($akun->jrdt_dk == 'D') ? DB::raw('as_mut_memorial_debet - '.$akun->jrdt_value) : DB::raw('as_mut_memorial_debet'),

						'as_mut_memorial_kredit' => ($akun->jrdt_dk == 'K') ? DB::raw('as_mut_memorial_kredit - '.$akun->jrdt_value) : DB::raw('as_mut_memorial_kredit')
					]);
					break;
			}

			if($cek->ak_posisi == 'D'){
					$equivalent = ($akun->jrdt_dk == 'D') ? ($akun->jrdt_value * -1) : $akun->jrdt_value;
			}else{
					$equivalent = ($akun->jrdt_dk == 'K') ? ($akun->jrdt_value * -1) : $akun->jrdt_value;
			}

			DB::table('dk_akun_saldo')->where('as_periode', $periode)->where('as_akun', $akun->jrdt_akun)->update([
				"as_saldo_akhir" => DB::raw('as_saldo_akhir + '.$equivalent)
			]);

			DB::table('dk_akun_saldo')->where('as_periode', '>', $periode)->where('as_akun', $akun->jrdt_akun)->update([
				"as_saldo_awal"	 => DB::raw('as_saldo_awal + '.$equivalent),
				"as_saldo_akhir" => DB::raw('as_saldo_akhir + '.$equivalent)
			]);
		}

		// return json_encode($type);
	}

}