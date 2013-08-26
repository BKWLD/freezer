# Freezer

Freezer creates full page caches that are **directly** serve-able by Apache.  In other words, once a cache is created, PHP doesn't even load to return that cache.  Thus, you save the overhead of the whole Laravel stack booting up in addition to eliminating the cost of your application code, database queries, etc.

Freezer does this by creating cache files that can be found directly by Apache through special htaccess rules.  Pruning of stale caches is done either by executing of CLI commands (like from cron) or by invoking Freezer's API.

The use-case that Freezer was designed for was small sites that don't see a ton of updates in the CMS.  Apache serving a static HTML file can speed up the rendering of pages from 300ms to 3ms.

## Installation

1. Add it to your composer.json (`"bkwld/freezer": "~1.0"`) and do a composer install.

2. Add the service provider to your app.php config file providers: `'Bkwld\Freezer\ServiceProvider',`

3. Add the facade to your app.php config file's aliases: `'Freezer' => 'Bkwld\Freezer\Facade',`

4. Add this to your public/.htaccess file **BEFORE** the Laravel rules involving index.php:

		# Serve Freezer full page cache files
		RewriteCond %{DOCUMENT_ROOT}/uploads/freezer/$0\.html -f
		RewriteRule ^.+$ uploads/freezer/$0.html [L]
		RewriteCond %{DOCUMENT_ROOT}/uploads/freezer/_homepage\.html -f
		RewriteRule ^$ uploads/freezer/_homepage.html [L]

5. Push config files to your app/config/packages directory for customization with `php artisan config:publish bkwld/freezer`

6. If you are going to use expiration times, setup a worker or cron job to run `php artisan freezer:prune` to delete stale caches.  Here is an example for cron that will check for stale caches every minute:

		* * * * * /path/to/php /path/to/artisan freezer:prune
		
## Config

* `dir` - The directory that you want Freezer to write it's cache files to.  It must be writeable and within the document root.  In other words, within you /public directory.

* `whitelist` - The a list of regex-like patterns that match URLs that should be cached.  For instance, if you want both /news and /news/15 to be cached, you would have an entry in the array for `news*`.  The pattern matching is done by Laravel's `Str::is()`.  If an entry in the array is just a pattern, the cache will never expire.  If you use a key-value pair of pattern-lifetime (where lifetime is in minutes), then you can have Freezer automatically expire your catch (as long as you have a Cron setup to auto-prune).  For example, `'news*' => 15` will expire all news caches after 15 minutes. 

* `blacklist` - The blacklist is processed after the whitelist.  Enter patterns that should NOT be cached.  For instance, if `news/20` is in the blacklist and `news*` is in the whitelist, then all news articles but the one with id 20 will be full page cached.

**Note**: There is a local/config.php file that comes in the package that disables Freezer locally (for your "local" enviornment) by having an empty whitelist.

## Usage

