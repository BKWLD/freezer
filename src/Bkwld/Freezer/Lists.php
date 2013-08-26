<?php namespace Bkwld\Freezer;

// Dependencies
use Str;

class Lists {
	
	/**
	 * Inject some dependencies
	 * @param array $whitelist
	 * @param array $blacklist
	 */
	private $white;
	private $black;
	public function __construct($whitelist, $blacklist) {
		$this->white = $whitelist;
		$this->black = $blacklist;
	}
	
		/**
	 * Check the path against the white or blacklist
	 * @param string $path
	 * @return false|null|int Only false means not found
	 */
	public function checkAndGetLifetime($path) {
		$lifetime = $this->checkWhiteAndGetLifetime($path);
		if ($lifetime !== false && $this->checkBlack($path) === false) return $lifetime;
		return false;
	}
	
	/**
	 * Check the whitelist
	 */
	public function checkWhiteAndGetLifetime($path) {
		
		// Loop through the list
		foreach($this->white as $key => $val) {
			
			// Figure out the lifetime
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
	 * Check the blacklist
	 */
	public function checkBlack($path) {
		foreach($this->black as $pattern) {
			if (Str::is($pattern, $path)) return $lifetime;
		}
		return false;
	}
	
}