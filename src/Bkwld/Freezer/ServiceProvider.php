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

		// Create caches by listening for a response
		if (count($config['whitelist'])) {
			$this->app->after(function($request, $response) use ($dir, $lists) {
				
				// Compare the URL to the 
				$create = new Create($response, $dir);
				$create->conditionallyCache($request, $lists);
				
			});
		}
		
		// Register commands.  Syntax from http://forums.laravel.io/viewtopic.php?pid=50215#p50215
		// When I was doing Artisan::add() I got seg fault 11.
		$this->app['command.freezer.clear'] = $this->app->share(function($app) use ($dir) {
			return new \Bkwld\Freezer\Commands\Clear($dir);
		});
		$this->app['command.freezer.prune'] = $this->app->share(function($app) use ($dir, $lists) {
			return new \Bkwld\Freezer\Commands\Prune($dir, $lists);
		});
		$this->commands(array('command.freezer.clear', 'command.freezer.prune'));
		
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