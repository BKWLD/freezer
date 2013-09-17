<?php namespace Bkwld\Freezer;
class Facade extends \Illuminate\Support\Facades\Facade {
	
	/**
	 * Clear an item from the cache
	 * @param string $delete A Str::is() style regexp matching the request path that was cached
	 * @param number $lifetime Only clear if the cache was created less than this lifetime
	 */
	public static function clear($pattern = null, $lifetime = null) {
		return static::$app->make('freezer.delete')->clear($pattern, $lifetime);
	}
	
	/**
	 * Rebuild an item from the cache
	 * @param string $delete A Str::is() style regexp matching the request path that was cached
	 * @param number $lifetime Only clear if the cache was created less than this lifetime
	 */
	public static function rebuild($pattern = null, $lifetime = null) {
		return static::$app->make('freezer.delete')->rebuild($pattern, $lifetime);
	}
	
	/**
	 * Skip caching the next request
	 */
	public static function skipNext() {
		
		// Make sure this path is one that WOULD be cached ordinarily
		$path = static::$app->make('request')->path();
		if (static::$app->make('freezer.lists')->checkAndGetLifetime($path) === false) return;
		
		// Set cookie to skip next
		$cookie = static::$app->make('cookie')->make(ServiceProvider::SKIP_COOKIE, true);
		static::$app->after(function($request, $response) use ($cookie) {
			$response->withCookie($cookie);
		});
	}
	
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'freezer'; }
}