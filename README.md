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
		RewriteCond %{HTTP_COOKIE} !freezer-skip [NC]
		RewriteCond %{REQUEST_METHOD} GET
		RewriteCond %{DOCUMENT_ROOT}/uploads/freezer/$0\.html -f
		RewriteRule ^.+$ uploads/freezer/$0.html [L]
		RewriteCond %{HTTP_COOKIE} !freezer-skip [NC]
		RewriteCond %{REQUEST_METHOD} GET
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

## API

#### `Freezer::clear($pattern, $lifetime)`

Delete cache files that match a pattern or age

- `$pattern` [string] A `Str::is()` style regexp matching the request path that was cached
- `$lifetime` [number] Only clear if the cache was created less than this lifetime

#### `Freezer::rebuild($pattern, $lifetime)`

Rebuild cache files that match a pattern or age.  This works by simulating a GET request to the same route and replacing the cache with the response.

- `$pattern` [string] A `Str::is()` style regexp matching the request path that was cached
- `$lifetime` [number] Only clear if the cache was created less than this lifetime

#### `Freezer::skipNext()`

Adds a cookie to the response that tells Apache not to use the cache to respond to the *next* request.  Freezer then deletes this cookie, meaning *only* the subsequent request will be skipped.

## Commands

#### `php artisan freezer:clear`

Delets all cache files

#### `php artisan freezer:prune`

Deletes only the cache files that have expired based on your config file rules.

## Usage

As mentioned, in the introduction, the primary use-case this packages was designed for is sites that don't receive a ton of updates.  In other words, not user-generated-content based sites.  One easy was to get up and runnig with Freezer is to put this in your app/start/global.php file:

	// Delete all Freezer caches when a model changes
	// - $m is the model instance that is being acted upon
	// - $e is the event name (ex: "e:eloquent.saved: Article"
	Event::listen('eloquent.saved*', function($m, $e) { Freezer::rebuild(); });
	Event::listen('eloquent.deleted*', function($m, $e) { Freezer::rebuild(); });

This snippet will dump **all** of the cache whenever you create, update, or delete rows from your database.  Combine this with a whitelist on everything (`*`) except your admin directory (blacklist `admin*`) and you have a system where all your front-facing pages will get cached but will still immediately see any changes made in your admin.  You don't even need to setup a cron job with this approach.

Another handy thing to have in your app/start/global.php is this:

	// Skip caching the next page after any non-GET.  For instance, don't cache
	// the page that is shown after submitting a form
	if (Request::getMethod() != 'GET') Freezer::skipNext();

This will skip caching or serving all requests that follow a POST, PUT, or DELETE.

Remember to clear your Freezer cache on the server (`php artisan freezer:clear`) when deploying new code.
