<?php namespace Bkwld\Freezer\Commands;

// Dependencies
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
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		//
		Log::info('Running the example');
		echo 'Running the example';
	}

}