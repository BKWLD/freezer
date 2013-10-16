<?php

use \Mockery as m;
use Bkwld\Freezer\Queue;

class TestQueue extends PHPUnit_Framework_TestCase {
	
	// Build an instance
	private function build() {
		return new Queue(m::mock('Delete'));
	}
	
	/**
 	 * @expectedException Bkwld\Freezer\Exception
	 */
	public function testBadOperation() {
		$queue = $this->build();
		$queue->add('fart');
	}
	
	public function testAdd() {
		$queue = $this->build();
		$queue->add('rebuild');
		$this->assertEquals(1, $queue->count());
	}
	
	public function testSimpleDedupe() {
		$queue = $this->build();
		$queue->add('rebuild');
		$queue->add('rebuild');
		$this->assertEquals(1, $queue->count());
	}
	
	public function testComplexDedupe() {
		$queue = $this->build();
		$queue->add('rebuild');
		$queue->add('rebuild', 'whatever*');
		$queue->add('rebuild', 'whatever*');
		$queue->add('rebuild', 'whatever*');
		$queue->add('rebuild', 'whatever*', 5);
		$queue->add('rebuild', 'whatever*', 5);
		$queue->add('rebuild', null, 5);
		$this->assertEquals(4, $queue->count());
	}
	
}