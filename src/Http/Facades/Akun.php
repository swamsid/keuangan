<?php

namespace Swamsid\Keuangan\Http\Facades;

use Illuminate\Support\Facades\Facade;

class Akun extends Facade {
	protected static function getFacadeAccessor(){
		return 'Akun';
	}
}