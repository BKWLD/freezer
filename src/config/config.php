<?php return array(

	/**
	 * Directory to store the caches. Must be writeable and within the document
	 * root.
	 */
	'dir' => public_path().'/uploads/freezer',

	/**
	 * List URL patterns that should be cached. Do not include a leading slash.
	 * 
	 * Items can just be a simple string path or a key value pair with the
	 * key being the path pattern and the value being the expiration time for
	 * the cache in MINUTES.  Put more restrictive patterns first.
	 */
	'whitelist' => array(
		// 'about.*' => 15, // Cache about and sub pages for 15 minutes
		'*',               // Whitelist everything
	),
	
	/**
	 * The blacklist rules are processed after the whitelist and let you deny
	 * URLs that would otherwise be whitelisted. This does not support key value
	 * pairs.
	 */
	'blacklist' => array(
		'admin*',          // Don't cache admin pages
	),

);