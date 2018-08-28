<?php

namespace Conv\Laravel;

use Illuminate\Support\ServiceProvider;

class ConvServiceProvider extends ServiceProvider
{
	/**
	 * @return void
	 */
	public function boot()
	{
		if (!$this->isLumen()) {
			$this->publishes([
				// __DIR__.'/path/to/views' => base_path('resources/views/vendor/courier'),
				__DIR__ . '/../config/conv.php' => config_path('conv.php'),
			], 'conv');
		}
	}

	/**
	 * @return void
	 */
	public function register()
	{
		$this->commands([

		]);
	}

	/**
	 * @return bool
	 */
	protected function isLumen()
	{
		return str_contains($this->app->version(), 'Lumen');
	}
}