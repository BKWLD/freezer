<?php namespace Bkwld\Freezer\Commands;

// Dependencies
use Bkwld\Freezer\Delete;
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
		$delete = new Delete($this->dir);
		$this->info($delete->clear().' cache files or folders deleted');
	}

}