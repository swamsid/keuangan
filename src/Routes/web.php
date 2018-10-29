<?php

use Swamsid\Keuangan\Http\Facades\Akun;

Route::get('keuanganConnection', function(){
	return "connected";
});

Route::get('StatusAkun', function(){
	return Akun::getStatus();
});