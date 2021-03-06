<?php namespace Bkwld\Freezer;

// Dependencies
use Log;

class Create {
	
	/**
	 * Inject some dependencies
	 * @param Illuminate\Http\Response $response
	 * @param string $dir The directory to store cache files
	 */
	private $response;
	private $dir;
	public function __construct($response, $dir) {
		$this->response = $response;
		$this->dir = $dir;
	}
	
	/**
	 * Conditionally create a cache file if the request path
	 * matches the whitelist and not the blacklist
	 * @param Illuminate\Http\REquest $request
	 * @param Bkwld\Freezer\Lists $lists
	 * @param Illuminate\Cookie\CookieJar $cookies
	 */
	public function conditionallyCache($request, $lists, $cookies) {
		
		// Only cache HTML responses from Laravel
		if ($this->response->headers->has('content-type') 
			&& strpos($this->response->headers->get('content-type'), 'text/html') === false) return false;

		// .. also check for content type set outside of Laravel
		foreach(headers_list() as $header) {
			if (strpos(strtolower($header), 'content-type') !== false 
				&& strpos(strtolower($header), 'text/html') === false) return false;
		}

		// Check if Freezer has been disabled via the `disable()` api
		if ($request->hasSession() && $request->getSession()->has(Facade::DISABLE_KEY)) {
			return false;
		}

		// Determine if we have been instructed to skip this request.  Skipping
		// only affects a single request, so delete the cookie imediately
		if ($request->cookie(Facade::SKIP_COOKIE)) {
			$this->response->withCookie($cookies->forget(Facade::SKIP_COOKIE));
			return false;
		}
		
		// Only allow GETs
		if ($request->getMethod() != 'GET') return false;
		
		// Only allow requests with NO arguments, since we don't namespace
		// the caches with the GET args
		$input = $request->input();
		if (!empty($input)) return false;
		
		// Check white and blacklists
		$path = $request->path();
		$lifetime = $lists->checkAndGetLifetime($path);
		if ($lifetime !== false) {
			
			// Create the cache
			$this->cache($path, $lifetime);
		}
	}
	
	/**
	 * Create the cache file
	 * @param string $path
	 * @param number $lifetime Expiration time in minutes
	 */
	public function cache($path, $lifetime = null) {
		
		// Handle homepage
		if ($path == '/') $path = '_homepage';
		
		// Create subdirectories recursively
		$dir = dirname($path);
		if ($dir == '.') $dir = $this->dir; // If no parents, it would have been '.'
		else $dir = $this->dir.DIRECTORY_SEPARATOR.$dir;
		if (!file_exists($dir) && mkdir($dir, 0775, true) === false) {
			throw new Exception($dir.' directory could not be created');
		}
		
		// Write the HTML file
		$file = basename($path).'.html';
		if (file_put_contents($dir.DIRECTORY_SEPARATOR.$file, $this->response->getContent()) === false) {
			throw new Exception($dir.'/'.$file.' cache could not be written');
		}
		
		// Note that a caching has occured
		Log::debug("Cache created for '$path' at {$dir}/{$file}. Lifetime is ".($lifetime?:'infinite.'));
		
	}
	
}
