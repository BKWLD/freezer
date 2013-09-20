<?php namespace Bkwld\Freezer;

// Dependencies
use Illuminate\Foundation\Testing\Client;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

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
		$this->app->instance('freezer.lists', $lists);
		
		// Register delete instance
		$this->app->singleton('freezer.delete', function($app) use ($dir) {
			return new Delete($dir, new Client($app), $app['url']->to('/'));
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
		
	}
	
	/**
	 * Register event listeners
	 */
	public function boot() {
		
		// Get config (again)
		$config = $this->app->make('config')->get('freezer::config');
		$dir = $config['dir'];
		
		// Create caches by listening for the laravel lifecyle response as long as there
		// is a whitelist
		if (count($config['whitelist'])) {
			$lists = $this->app->make('freezer.lists');
			$cookies = $this->app->make('cookie');
			$this->app->after(function($request, $response) use ($dir, $lists, $cookies) {
				
				// Init create class and check if we should cache this request
				$create = new Create($response, $dir);
				$create->conditionallyCache($request, $lists, $cookies);
			});
		}
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array('freezer', 'freezer.delete', 'freezer.lists', 'command.freezer.clear', 'command.freezer.prune');
	}

}