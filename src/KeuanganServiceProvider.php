<?php

namespace Swamsid\Keuangan;

use Illuminate\Support\ServiceProvider;

class KeuanganServiceProvider extends ServiceProvider {
	public function boot(){

	}

	public function register(){
		$this->app->bind('keuangan', function(){
			return new Constructor\keuangan;
		});
	}
}