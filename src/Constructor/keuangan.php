<?php

namespace Swamsid\Keuangan\Constructor;


class keuangan{

	public function connection(){
		return new Func\connection;
	}

	public function periode(){
		return new Func\Periode\idle;
	}

	public function akunSaldo(){
		return new Func\Saldo_akun\idle;
	}

	public function jurnal(){
		return new Func\Jurnal\idle;
	}

}