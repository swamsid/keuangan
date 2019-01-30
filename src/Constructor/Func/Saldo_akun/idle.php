<?php

namespace Swamsid\Keuangan\Constructor\Func\Saldo_akun;

use App\Model\modul_keuangan\dk_akun as akun;
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

	public function updateAkun(String $akun){
		$dataAkun = DB::table('dk_akun')->where('ak_id', $akun)->first();
		$dataSaldo = DB::table('dk_akun_saldo')->where('as_akun', $akun)->get();

		$cekkl = []; $plus = $minus = 0;

		foreach($dataSaldo as $key => $saldo){
			$dateBefore = date('Y-m-d', strtotime('-1 months', strtotime($saldo->as_periode)));
			$dataBefore = DB::table('dk_akun_saldo')->where('as_periode', $dateBefore)->where('as_akun', $saldo->as_akun)->first();
			$SA = ($dataBefore) ? $dataBefore->as_saldo_akhir : 0;

			if($key == 0)
				$SA = $dataAkun->ak_opening;

			if($dataAkun->ak_posisi == 'D'){
				$plus = ($saldo->as_mut_kas_debet + $saldo->as_mut_bank_debet + $saldo->as_mut_memorial_debet);
				$minus = ($saldo->as_mut_kas_kredit + $saldo->as_mut_bank_kredit + $saldo->as_mut_memorial_kredit);
			}else{
				$plus = ($saldo->as_mut_kas_kredit + $saldo->as_mut_bank_kredit + $saldo->as_mut_memorial_kredit);
				$minus = ($saldo->as_mut_kas_debet + $saldo->as_mut_bank_debet + $saldo->as_mut_memorial_debet);
			}

			$bucket = [
				'as_saldo_awal' => $SA,
				'as_saldo_akhir' => $SA + ($plus - $minus),
			];

			$cekkl[$key] = [
				'as_saldo_awal' => $SA,
				'as_saldo_akhir' => $SA + ($plus - $minus),
				'plus'	=> $plus,
				'minus'	=> $minus,
			];

			DB::table('dk_akun_saldo')->where('as_periode', $saldo->as_periode)->where('as_akun', $akun)->update($bucket);
		}


		// return json_encode(DB::table('dk_akun_saldo')->where('as_akun', $akun)->get());
	}

	public function addNewPeriode(String $periode){
		// return json_encode($periode);

		$akunBucket = [];
		$periodeNext = date('Y-m-d', strtotime('+1 months', strtotime($periode)));
		$periodeLast = date('Y-m-d', strtotime('-1 months', strtotime($periode)));

		$akun = akun::select('ak_id', 'ak_opening', 'ak_posisi')
						->with([
							'mutasiKasMasuk' => function($query) use ($periode, $periodeNext){
								$query->whereIn('jrdt_jurnal', function($query) use ($periode, $periodeNext){
									$query->select('jr_id')
												->from('dk_jurnal')
												->where('jr_tanggal_trans', '>=', $periode)
												->where('jr_tanggal_trans', '<', $periodeNext)
												->where(DB::raw('SUBSTR(jr_type, 1, 1)'), 'K')
												->get();
								})
								->where('jrdt_dk', 'D')
								->select('jrdt_akun', DB::raw('sum(jrdt_value) as jrdt_value'))
								->groupBy('jrdt_akun')->get();
							},
							'mutasiKasKeluar' => function($query) use ($periode, $periodeNext){
								$query->whereIn('jrdt_jurnal', function($query) use ($periode, $periodeNext){
									$query->select('jr_id')
												->from('dk_jurnal')
												->where('jr_tanggal_trans', '>=', $periode)
												->where('jr_tanggal_trans', '<', $periodeNext)
												->where(DB::raw('SUBSTR(jr_type, 1, 1)'), 'K')
												->get();
								})
								->where('jrdt_dk', 'K')
								->select('jrdt_akun', DB::raw('sum(jrdt_value) as jrdt_value'))
								->groupBy('jrdt_akun')->get();
							},
							'mutasiBankMasuk' => function($query) use ($periode, $periodeNext){
								$query->whereIn('jrdt_jurnal', function($query) use ($periode, $periodeNext){
									$query->select('jr_id')
												->from('dk_jurnal')
												->where('jr_tanggal_trans', '>=', $periode)
												->where('jr_tanggal_trans', '<', $periodeNext)
												->where(DB::raw('SUBSTR(jr_type, 1, 1)'), 'B')
												->get();
								})
								->where('jrdt_dk', 'D')
								->select('jrdt_akun', DB::raw('sum(jrdt_value) as jrdt_value'))
								->groupBy('jrdt_akun')->get();
							},
							'mutasiBankKeluar' => function($query) use ($periode, $periodeNext){
								$query->whereIn('jrdt_jurnal', function($query) use ($periode, $periodeNext){
									$query->select('jr_id')
												->from('dk_jurnal')
												->where('jr_tanggal_trans', '>=', $periode)
												->where('jr_tanggal_trans', '<', $periodeNext)
												->where(DB::raw('SUBSTR(jr_type, 1, 1)'), 'B')
												->get();
								})
								->where('jrdt_dk', 'K')
								->select('jrdt_akun', DB::raw('sum(jrdt_value) as jrdt_value'))
								->groupBy('jrdt_akun')->get();
							},
							'mutasiMemorialDebet' => function($query) use ($periode, $periodeNext){
								$query->whereIn('jrdt_jurnal', function($query) use ($periode, $periodeNext){
									$query->select('jr_id')
												->from('dk_jurnal')
												->where('jr_tanggal_trans', '>=', $periode)
												->where('jr_tanggal_trans', '<', $periodeNext)
												->where(DB::raw('SUBSTR(jr_type, 1, 1)'), 'M')
												->get();
								})
								->where('jrdt_dk', 'D')
								->select('jrdt_akun', DB::raw('sum(jrdt_value) as jrdt_value'))
								->groupBy('jrdt_akun')->get();
							},
							'mutasiMemorialKredit' => function($query) use ($periode, $periodeNext){
								$query->whereIn('jrdt_jurnal', function($query) use ($periode, $periodeNext){
									$query->select('jr_id')
												->from('dk_jurnal')
												->where('jr_tanggal_trans', '>=', $periode)
												->where('jr_tanggal_trans', '<', $periodeNext)
												->where(DB::raw('SUBSTR(jr_type, 1, 1)'), 'M')
												->get();
								})
								->where('jrdt_dk', 'K')
								->select('jrdt_akun', DB::raw('sum(jrdt_value) as jrdt_value'))
								->groupBy('jrdt_akun')->get();
							},
						])
						->get();

						// return json_encode($akun);

		foreach($akun as $key => $dataAkun){
			
			$SA = $dataAkun->ak_opening;
			$cek = DB::table('dk_akun_saldo')->where('as_periode', $periodeLast)->where('as_akun', $dataAkun->ak_id)->first();

			if($cek){
				$SA = $cek->as_saldo_akhir;
			}

			$KM = (count($dataAkun->mutasiKasMasuk)) ? $dataAkun->mutasiKasMasuk[0]->jrdt_value : 0;
			$KK = (count($dataAkun->mutasiKasKeluar)) ? $dataAkun->mutasiKasKeluar[0]->jrdt_value : 0;
			$BM = (count($dataAkun->mutasiBankMasuk)) ? $dataAkun->mutasiBankMasuk[0]->jrdt_value : 0;
			$BK = (count($dataAkun->mutasiBankKeluar)) ? $dataAkun->mutasiBankKeluar[0]->jrdt_value : 0;
			$MD = (count($dataAkun->mutasiMemorialDebet)) ? $dataAkun->mutasiMemorialDebet[0]->jrdt_value : 0;
			$MK = (count($dataAkun->mutasiMemorialKredit)) ? $dataAkun->mutasiMemorialKredit[0]->jrdt_value : 0;

			$saldoAkhir = 0;

			if($dataAkun->ak_posisi == 'D')
				$saldoAkhir = $SA + (($KM + $BM + $MD) - ($KK + $BK + $MK));
			else
				$saldoAkhir = $SA + (($KK + $BK + $MK) - ($KM + $BM + $MD));

			$akunBucket[$key] = [
				"as_akun"					=> $dataAkun->ak_id,
				"as_periode"				=> $periode,
				"as_saldo_awal"				=> $SA,
				"as_mut_kas_debet"			=> $KM,
				"as_mut_kas_kredit"			=> $KK,
				"as_mut_bank_debet"			=> $BM,
				"as_mut_bank_kredit"		=> $BK,
				"as_mut_memorial_debet"		=> $MD,
				"as_mut_memorial_kredit"	=> $MK,
				"as_saldo_akhir" 			=> $saldoAkhir,
			];
		}

		// return json_encode($akunBucket);

		DB::table('dk_akun_saldo')->insert($akunBucket);
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