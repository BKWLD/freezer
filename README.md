# Freezer

Freezer creates full page caches that are **directly** serve-able by Apache.  In other words, once a cache is created, PHP doesn't even load to return that cache.  Thus, you save the overhead of the whole Laravel stack booting up in addition to eliminating the cost of your application code, database queries, etc.  It does this by creating cache files that can be found directly by Apache through special htaccess rules.  Pruning of stale caches is done either by executing of CLI commands (like from cron) or by invoking Freezer's API.

## Installation

1. Add it to your composer.json: "bkwld/freezer": "~1.0"
2. Add the service provider to you app.php config file providers: `'Bkwld\Freezer\ServiceProvider',`
3. Add the facade to your app.php config file's aliases: `'Freezer' => 'Bkwld\Freezer\Facade',`
2. Add this to your public/.htaccess file **BEFORE** the Laravel rules involving index.php:

		# Serve Freezer full page cache files
		RewriteCond %{DOCUMENT_ROOT}/uploads/freezer/$0\.html -f
		RewriteRule ^.+$ uploads/freezer/$0.html [L]
		RewriteCond %{DOCUMENT_ROOT}/uploads/freezer/_homepage\.html -f
		RewriteRule ^$ uploads/freezer/_homepage.html [L]