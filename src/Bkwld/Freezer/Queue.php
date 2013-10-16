<?php namespace Bkwld\Freezer;

// Dependencies

class Queue {
	
	/**
	 * The actual queue is stored here
	 */
	private $queue = array();
	
	/**
	 * Inject Dependencies
	 * @param Bkwld\Freezer\Delete $delete
	 */
	private $delete;
	public function __construct($delete) {
		$this->delete = $delete;
	}
	
	/**
	 * Add an operation to the queue
	 * @param string $operation clear|rebuild
	 * @param string $pattern A Str::is() style regexp matching the request path that was cached
	 * @param number $lifetime Only clear if the cache was created less than this lifetime
	 */
	public function add($operation, $pattern = null, $lifetime = null) {
		
		// Validate the operation
		if (!preg_match('#^(clear|rebuild)\z#', $operation)) {
			throw new Exception('Queue::add() $operation must be "clear" or "rebuild"');
		}
		
		// Dedupe the incoming operation
		$key = $operation.'|'.$pattern.'|'.$lifetime;
		if (in_array($key, array_keys($this->queue))) return false;
		
		// Add the operation to the queue
		$this->queue[$key] = func_get_args();
	}
	
	/**
	 * Iterate through the queue and execute the queued operations
	 */
	public function process() {
		$count = 0;
		foreach($this->queue as $args) {
			$count += call_user_func_array(array($this->delete, $args[0]), array_slice($args, 1));
		}
		return $count;
	}
	
	/**
	 * Get the length of the queue, this is mainly for unit testing
	 * @return number The length of the queue
	 */
	public function count() {
		return count($this->queue);
	}
	
}