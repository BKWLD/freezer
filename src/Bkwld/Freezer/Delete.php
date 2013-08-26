<?php namespace Bkwld\Freezer;

// Dependencies
use Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Str;

class Delete {
	
	/**
	 * Inject some dependencies
	 * @param string $dir The directory to store cache files
	 */
	private $dir;
	public function __construct($dir) {
		$this->dir = $dir;
	}
	
	/**
	 * Delete cache files that match a pattern
	 * @param string $delete A Str::is style regexp to restrict deleting to
	 */
	public function clear($pattern = null, $lifetime = null) {
		
		// Loop through directory
		$i = 0;
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir), RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $f) {
			
			// Check if the pattern matches
			$path = $f->getRealPath();
			if ($pattern && !Str::is($this->dir.'/'.$pattern, $path)) continue;
			
			// See if the file or directory has expired
			if ($lifetime && $f->getMTime() > time() - $lifetime*60) continue;
			
			// Delete the file or directory
			if($f->isFile()) {
				if (!unlink($path)) throw new Exception($path.' could not be deleted');
				$i++;
			} else if($f->isDir() && !$files->hasChildren()) {
				if (!rmdir($path)) throw new Exception($path.' could not be deleted');
				$i++;
			}
		}
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
	
}