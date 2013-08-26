<?php namespace Bkwld\Freezer;

// Dependencies
use Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
				if (!unlink($f->getRealPath())) throw new Exception('File could not be deleted');
				$i++;
			} else if($f->isDir()) {
				if (!rmdir($f->getRealPath())) throw new Exception('Directory could not be deleted');
				$i++;
			}
		}
		return $i;
	}
	
	/**
	 * Delete only expired cached files
	 */
	public function prune() {
		
	}
	
}