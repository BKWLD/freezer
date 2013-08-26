# Freezer

## Installation

Add this to your public/.htaccess file **BEFORE** the Laravel rules involving index.php:

	# Serve Freezer full page cache files
	RewriteCond %{DOCUMENT_ROOT}/uploads/page_cache/$0\.html -f
	RewriteRule ^.+$ uploads/page_cache/$0.html [L]
	RewriteCond %{DOCUMENT_ROOT}/uploads/page_cache/_homepage\.html -f
	RewriteRule ^$ uploads/page_cache/_homepage.html [L]