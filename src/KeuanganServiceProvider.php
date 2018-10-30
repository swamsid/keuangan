<?php

namespace Swamsid\Keuangan;

use Illuminate\Support\ServiceProvider;

class KeuanganServiceProvider extends ServiceProvider {
	public function boot(){
		$this->loadRoutesFrom(__DIR__.'/Routes/web.php');
		$this->loadMigrationsFrom(__DIR__.'/Http/Migrations');

		$this->publishes([
			__DIR__.'/Http/Controllers/Publishes' => app_path('/Http/Controllers/Keuangan'),
			__DIR__.'/Http/Model/Publishes' => app_path('Model/Keuangan'),
			__DIR__.'/Http/Migrations/Publishes' => database_path('migrations/swamsid-keuangan'),
			__DIR__.'/Views' => resource_path('views/keuangan'),
		]);
	}

	public function register(){
		// $this->app->bind('Akun', function(){
		// 	return new Constructor\Akun;
		// });
	}
}