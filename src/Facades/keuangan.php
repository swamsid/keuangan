<?php

namespace Swamsid\Keuangan\Facades;

use Illuminate\Support\Facades\Facade;

class keuangan extends Facade{
	protected static function getFacadeAccessor(){
		return 'keuangan';
	}
}