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
	 * Delete ALL cache files. Some code from: http://stackoverflow.com/a/5769525/59160
	 */
	public function clear() {
		$i = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
			if($f->isFile()) {
				if (!unlink($f->getRealPath())) throw new Exception($f->getRealPath().' could not be deleted');
				$i++;
			} else if($f->isDir()) {
				if (!rmdir($f->getRealPath())) throw new Exception($f->getRealPath().' could not be deleted');
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
			
			// Loop through files
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
				
				// Check if file matches the pattern
				$path = $f->getRealPath();
				if (!Str::is($this->dir.'/'.$pattern, $path)) continue;
				
				// See if the pattern has expired
				if ($f->getMTime() > time() - $lifetime*60) continue;
				
				// Delete the file
				if (!unlink($path)) throw new Exception($path.' could not be deleted');
				$i++;
				
			}
		}
		
		// Return total deleted
		return $i;
		
	}
	
}