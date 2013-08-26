<?php namespace Bkwld\Freezer\Commands;

// Dependencies
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Illuminate\Console\Command;

class Clear extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'freezer:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete ALL full page cache files';

	/**
	 * Inject some dependencies
	 */
	private $dir;
	public function __construct($dir) {
		$this->dir = $dir;
		parent::__construct();
		
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() {
		
		// Delete all the files in the dir.  Some code from:
		// http://stackoverflow.com/a/5769525/59160
		$i = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
			if($f->isFile()) {
				unlink($f->getRealPath());
				$i++;
			} else if($f->isDir()) {
				rmdir($f->getRealPath());
				$i++;
			}
		}
		
		// Output status
		$this->info($i.' cache files or folders deleted');
		
	}

}