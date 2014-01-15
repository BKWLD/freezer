<?php namespace Bkwld\Freezer;
class Facade extends \Illuminate\Support\Facades\Facade {
	
	/**
	 * Shared constants
	 */
	const SKIP_COOKIE = 'freezer-skip'; // The cookie name
	const DISABLE_KEY = 'freezer-disable';
	const USER_AGENT = 'BKWLD/Freezer'; // The user agent that the rebuilds come from
	
	/**
	 * Clear an item from the cache
	 * @param string $pattern A Str::is() style regexp matching the request path that was cached
	 * @param number $lifetime Only clear if the cache was created less than this lifetime
	 */
	public static function clear($pattern = null, $lifetime = null) {
		return static::$app->make('freezer.delete')->clear($pattern, $lifetime);
	}
	
	/**
	 * Rebuild an item from the cache
	 * @param string $pattern A Str::is() style regexp matching the request path that was cached
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
		
		// Set cookie to skip-next
		self::makeSkipNextCookie();
	}

	/**
	 * Create the skip next cookie
	 */
	private static function makeSkipNextCookie() {
		$cookie = static::$app->make('cookie')->make(self::SKIP_COOKIE, true);
		static::$app->after(function($request, $response) use ($cookie) {
			$response->withCookie($cookie);
		});
	}
	
	/**
	 * Don't allow duplicate calls to clear or rebuild to be executed on the same request
	 * @param string $operation clear|rebuild
	 * @param string $pattern A Str::is() style regexp matching the request path that was cached
	 * @param number $lifetime Only clear if the cache was created less than this lifetime
	 */
	public static function debounce($operation, $pattern = null, $lifetime = null) {
		return static::$app->make('freezer.queue')->add($operation, $pattern, $lifetime);
	}

	/**
	 * Disable Freezer from generating cache files until Enable is called again
	 */
	public static function disable() {

		// Use the skip-next cookie to stop Freezer from creating caches
		self::makeSkipNextCookie();

		// Set a session variable to prevent Freezer from clearing the skip-next cookie
		static::$app->make('session')->put(self::DISABLE_KEY, true);
	}

	/**
	 * Re-enable Freezer to create caches.  In other words, restore default behavior following
	 * a disable() call
	 */
	public static function enable() {

		// Remove the disable session var
		static::$app->make('session')->forget(self::DISABLE_KEY);

	}

	/**
	 * Determine whether Freezer is enabled or not
	 * @return boolean 
	 */
	public static function enabled() {
		return !static::$app->make('session')->has(self::DISABLE_KEY);
	}
	
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'freezer'; }
}