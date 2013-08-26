<?php namespace Bkwld\Freezer\Commands;

// Dependencies
use Bkwld\Freezer\Delete;
use Illuminate\Console\Command;

class Prune extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'freezer:prune';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete expired full page cache files';

	/**
	 * Inject some dependencies
	 * @param string $dir
	 * @param Bkwld\Freezer\Lists $lists
	 */
	private $dir;
	private $lists;
	public function __construct($dir, $lists) {
		$this->dir = $dir;
		$this->lists = $lists;
		parent::__construct();
		
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() {
		$delete = new Delete($this->dir);
		$this->info($delete->prune($this->lists).' expired cache files deleted');
	}

}