<?php namespace Bkwld\Freezer;

use Illuminate\Support\ServiceProvider;

class FreezerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		$this->package('bkwld/freezer');
		
		// Get freezer config
		$config = $this->app->make('config')->get('freezer::config');

		// Create caches by listening for a response
		if (count($config['whitelist'])) {
			$this->app->after(function($request, $response) use ($config) {
				
				// Compare the URL to the 
				$create = new Create($response, $config['dir']);
				$create->conditionallyCache($request, $config['whitelist'], $config['blacklist']);
				
			});
		}
		
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array('freezer');
	}

}