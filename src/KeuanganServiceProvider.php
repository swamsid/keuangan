<?php

namespace Swamsid\Keuangan;

use Illuminate\Support\ServiceProvider;

class KeuanganServiceProvider extends ServiceProvider {
	public function boot(){
		$this->loadRoutesFrom(__DIR__.'/Routes/web.php');
	}

	public function register(){
		$this->app->bind('Akun', function(){
			return new Constructor\Akun;
		});
	}
}