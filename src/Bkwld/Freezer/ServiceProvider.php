<?php namespace Bkwld\Freezer;

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
			return new Delete($dir, $app);
		});
		
		// Register queue instance
		$this->app->singleton('freezer.queue', function($app) {
			return new Queue($app->make('freezer.delete'));
		});
		
		// Register commands.  Syntax from http://forums.laravel.io/viewtopic.php?pid=50215#p50215
		// When I was doing Artisan::add() I got seg fault 11.
		$this->app->singleton('command.freezer.clear', function($app) {
			return new Commands\Clear;
		});
		$this->app->singleton('command.freezer.prune', function($app) use ($lists) {
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
			$queue = $this->app->make('freezer.queue');
			$this->app->after(function($request, $response) use ($dir, $lists, $cookies, $queue) {
				
				// Iterate through the queue of operations and do clears or rebuilds.  Check that
				// we're not currently fielding a request from Freezer, though. Otherwise infitine
				// loops
				if (!preg_match('#'.preg_quote(Facade::USER_AGENT, '#').'#', $request->header('user-agent'))) {
					$queue->process();
				}
				
				// Init create class and check if we should cache this request
				$create = new Create($response, $dir, $this->app);
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
		return array('freezer', 'freezer.delete', 'freezer.lists', 'freezer.queue', 'command.freezer.clear', 'command.freezer.prune');
	}

}
