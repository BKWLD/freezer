<?php namespace Bkwld\Freezer;

// Dependencies
use Log;
use Str;

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