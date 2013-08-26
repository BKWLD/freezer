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
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() {
		$this->info($this->getLaravel()->make('freezer.delete')->clear().' cache files or folders deleted');
	}

}