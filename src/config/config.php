<? return array(

	/**
	 * Directory to store the caches. Must be writeable and within the document
	 * root.
	 */
	'dir' => public_path().'/uploads/freezer',

	/**
	 * List URL paths that should be cached.  These will be parsed against
	 * Laravel's Str::is() function, so simple * or full regexp can be used.
	 * This also means that they must include the beginning.  Do not
	 * include a leading slash.
	 * 
	 * Items can just be a simple string path or a key value pair with the
	 * key being the path pattern and the value being the expiration time for
	 * the cache in MINUTES.  Put more restrictive patterns first.
	 */
	'whitelist' => array(
		'features*' => 15,
		'*',
	),
	
	/**
	 * The blacklist rules are processed after the whitelist and let you deny
	 * URLs that would otherwise be whitelisted. This does not support key value
	 * pairs.
	 */
	'blacklist' => array(
		'admin*',
		'about*',
	),

);