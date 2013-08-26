<?php namespace Bkwld\Freezer;
class Facade extends \Illuminate\Support\Facades\Facade {
	
	/**
	 * Clear an item from the cache
	 * @param string $pattern A regex-like pattern that validates against Str::is
	 */
	public static function clear($pattern = null, $lifetime = null) {
		return static::$app->make('freezer.delete')->clear($pattern, $lifetime);
	}
	
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'freezer'; }
}