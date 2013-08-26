<?php namespace Bkwld\Freezer;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

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
		$dir = $config['dir'];

		// Create a lists object
		$lists = new Lists($config['whitelist'], $config['blacklist']);
		
		// Register delete instance
		$this->app->singleton('freezer.delete', function($app) use ($dir) {
			return new Delete($dir);
		});
		
		// Register commands.  Syntax from http://forums.laravel.io/viewtopic.php?pid=50215#p50215
		// When I was doing Artisan::add() I got seg fault 11.
		$this->app->singleton('command.freezer.clear', function($app) use ($dir) {
			return new Commands\Clear;
		});
		$this->app->singleton('command.freezer.prune', function($app) use ($dir, $lists) {
			return new Commands\Prune($lists);
		});
		$this->commands(array('command.freezer.clear', 'command.freezer.prune'));
		
		// Create caches by listening for the laravel lifecyle response as long as there
		// is a whitelist
		if (count($config['whitelist'])) {
			$this->app->after(function($request, $response) use ($dir, $lists) {
				
				// Compare the URL to the 
				$create = new Create($response, $dir);
				$create->conditionallyCache($request, $lists);
				
			});
		}
		
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array('freezer', 'freezer.delete', 'command.freezer.clear', 'command.freezer.prune');
	}

}