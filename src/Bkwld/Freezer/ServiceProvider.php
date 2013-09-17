<?php namespace Bkwld\Freezer;

// Dependencies
use Illuminate\Foundation\Testing\Client;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * The cookie name
	 */
	const SKIP_COOKIE = 'freezer-skip';

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
		$dir = realpath($config['dir']);

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
		
		// Determine if we have been instructed to skip this request.  Skipping
		// only affects a single request, so delete the cookie imediately
		if ($skip = $this->app['cookie']->has(self::SKIP_COOKIE)) {
			$cookie = $this->app['cookie']->forget(self::SKIP_COOKIE);
			$this->app->after(function($request, $response) use ($cookie) {
				$response->withCookie($cookie);
			});
		}
		
		// Create caches by listening for the laravel lifecyle response as long as there
		// is a whitelist
		if (count($config['whitelist']) && !$skip) {
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
		return array('freezer', 'freezer.delete', 'freezer.lists', 'command.freezer.clear', 'command.freezer.prune');
	}

}