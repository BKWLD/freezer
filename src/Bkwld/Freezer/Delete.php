<?php namespace Bkwld\Freezer;

// Dependencies
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Illuminate\Foundation\Testing\Client;

class Delete {
	
	/**
	 * Inject some dependencies
	 * @param string $dir The directory to store cache files
	 * @param Illuminate\Foundation\Application $app
	 */
	private $dir;
	private $app;
	private $client; // Illuminate\Foundation\Testing\Client
	private $host; // string $host Like "http://whatever.com"
	public function __construct($dir, $app) {
		$this->dir = $dir;
		$this->app = $app;
		$this->client = new Client($app);
		$this->host = $app['url']->to('/');
	}
	
	/**
	 * Delete cache files that match a pattern or age
	 * @param string $delete A Str::is() style regexp matching the request path that was cached
	 * @param number $lifetime Only clear if the cache was created less than this lifetime
	 */
	public function clear($pattern = null, $lifetime = null) {
		$i = 0;
		foreach($this->filter($pattern, $lifetime) as $f) {
			$path = $f->getRealPath();
			
			// Delete the file
			if ($f->isFile()) {
				if (!unlink($path)) throw new Exception($path.' could not be deleted');
				$i++;
			
			// ... or directory.  Regarding glob ... hasChildren() was returning the correct val http://cl.ly/3F1g2A0E380r
			} else if ($f->isDir() && count(scandir($path)) == 2) { // 2 because "." and ".." will always be present
				if (!rmdir($path)) throw new Exception($path.' could not be deleted');
				$i++;
			}
		}
		return $i;
	}
	
	/**
	 * Rebuild cache files that match a pattern or age
	 * @param string $delete A Str::is() style regexp matching the request path that was cached
	 * @param number $lifetime Only clear if the cache was created less than this lifetime
	 */
	public function rebuild($pattern = null, $lifetime = null) {
		$i = 0;
		$input = $this->app['request']->input();
		foreach($this->filter($pattern, $lifetime) as $f) {
			if (!$f->isFile()) continue;
			
			// Get the relative path to the cache. This leaves a leading slash and removes the .html extension
			$path = substr($f->getRealPath(), strlen($this->dir), -5); // Has leading slash
			if ($path == '/_homepage') $path = '';
			$uri = $this->host.$path;
			
			// Simulate a request.  This also will trigger Freezer to generate a new cache automatically
			// for that URL.  So there is no need to deal with the response from the call.
			$server = array('HTTP_USER_AGENT' =>'Symfony2 BrowserKit '.Facade::USER_AGENT);
			$this->client->request('GET', $uri, array(), array(), $server);
			
		}
		
		// The simulated request wipes the Input data.  So replace it back to what it
		// was so the rest of the app can use.  For what it's worth, this didn't work
		// until I referenced the latest 'request' object by passing $app into this class.
		// If I built a request in the serivce provider and passed it to this class, this did
		// not work.  I think that the client->request creates to reques objects in the
		// application.
		$this->app['request']->replace($input);
		
		// Return the number rebuild
		return $i;
	}
	
	/**
	 * Delete only expired cached files
	 * @param Bkwld\Freezer\Lists $lists
	 */
	public function prune($list) {
		
		// Loop through whitelist items that have an expiration
		$i=0;
		foreach($list->expiringPatterns() as $pattern => $lifetime) {
			$i += $this->clear($pattern, $lifetime);
		}
		
		// Return total deleted
		return $i;
		
	}
	
	/**
	 * Check if files or directories in the cache directory match passed conditions
	 */
	private function filter($pattern = null, $lifetime = null) {
		$output = array();
		
		// Test whether the cache directory has been created
		if (!is_dir($this->dir)) return $output;
		
		// Loop through directory
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
			
			// File must be either an html file or a directory
			if (!$f->isDir() && !preg_match('#\.html\z#', $f->getFilename())) continue;
			
			// Check if the pattern matches
			if ($pattern && !preg_match('#^'.$this->dir.'/'.$pattern.'(\.html)?\z#', $f->getRealPath())) continue;
			
			// See if the file or directory has expired
			if ($lifetime && $f->getMTime() > time() - $lifetime*60) continue;
			
			// The file passes the check
			$output[] = $f;
		}
		
		// Return filtered list of files
		return $output;
	}
	
}