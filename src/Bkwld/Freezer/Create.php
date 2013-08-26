<?php namespace Bkwld\Freezer;

// Dependencies
use Str;

class Create {
	
	/**
	 * Inject some dependencies
	 * @param Illuminate\Http\Response $response
	 */
	private $response;
	public function __construct($response) {
		$this->response = $response;
	}
	
	/**
	 * Conditionally create a cache file if the request path
	 * matches the whitelist and not the blacklist
	 * @param Illuminate\Http\REquest $request
	 * @param array $whitelist
	 * @param array $blacklist
	 */
	public function conditionallyCache($request, $whitelist, $blacklist) {
		
		// Only allow GETs
		if ($request->getMethod() != 'GET') return false;
		
		// Check white and blaclists
		$path = $request->path();
		$lifetime = $this->check($path, $whitelist);
		if ($lifetime !== false && $this->check($path, $blacklist) === false) {
			
			// Create the back
			$this->cache($path, $lifetime);
		}
	}
	
	/**
	 * Check the path against the white or blacklist
	 * @param string $path
	 * @param array $list
	 * @return false|null|int Only false means not found
	 */
	public function check($path, $list) {
		
		// Loop through the list
		foreach($list as $key => $val) {
			
			// Figure out the lifetime (only applicable to whitelist)
			if (is_int($key)) {
				$lifetime = null;
				$pattern = $val;
			} else {
				$lifetime = $val;
				$pattern = $key;
			}
			
			// Check key against request
			if (Str::is($pattern, $path)) return $lifetime;
			
		}
		
		// Not found
		return false;
		
	}
	
	/**
	 * Create the cache file
	 * @param string $path
	 */
	public function cache($path, $lifetime = null) {
		\Log::info('cache it');
	}
	
}