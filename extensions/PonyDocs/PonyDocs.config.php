<?php

/**
 * PonyDocs Extension Configuration
 * 
 */

/**
 * Checks if constant is already defined
 */
function pdefine($constant, $value) {
	if (!defined($constant))
		define($constant, $value);
}

/**
 * Settings a user can alter, but do so in LocalSettings.php, NOT HERE
 */
pdefine('PONYDOCS_PRODUCT_LOGO_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/extensions/PonyDocs/images/pony.png');
pdefine('PONYDOCS_PDF_COPYRIGHT_MESSAGE', 'Copyright Your Company, Inc. All Rights Reserved');
pdefine('PONYDOCS_PDF_TITLE_IMAGE_PATH', '/extensions/PonyDocs/images/pony.png');
pdefine('PONYDOCS_ENABLE_BRANCHINHERIT_EMAIL', true);


/**
 * You really should not need to edit anything here!
 * 
 * Seriously! You have been warned.
 */

// Define your user groups here
pdefine('PONYDOCS_EMPLOYEE_GROUP', 'employees');
pdefine('PONYDOCS_BASE_AUTHOR_GROUP', 'docteam');
pdefine('PONYDOCS_BASE_PREVIEW_GROUP', 'preview');
pdefine('PONYDOCS_CRAWLER_AGENT_REGEX', '/gsa-crawler/');

// Define Namespace information
pdefine('PONYDOCS_DOCUMENTATION_NAMESPACE_NAME', 'Documentation');
pdefine('PONYDOCS_DOCUMENTATION_NAMESPACE_ID', 100);

// Default Language for the Site.
pdefine('PONYDOCS_LANGUAGE_DEFAULT', 'en'); 
// Automatically translate WikiDocs UI into documentations language format.
// TODO: Fix, Buggy -- This is buggy, not sure it should say in.
pdefine('PONYDOCS_LANGUAGE_AUTOUI', false);
// Always include language in URL, to include the default language.
pdefine('PONYDOCS_LANGUAGE_ALWAYS', false);

pdefine('PONYDOCS_CACHE_ENABLED', true);
pdefine('PONYDOCS_CACHE_DEBUG', false);
pdefine('PONYDOCS_REDIRECT_DEBUG', false);
pdefine('PONYDOCS_SESSION_DEBUG', false);
pdefine('PONYDOCS_AUTOCREATE_DEBUG', false);
pdefine('PONYDOCS_CASE_INSENSITIVE_DEBUG', false);
pdefine('PONYDOCS_DOCLINKS_DEBUG', false);

pdefine('PONYDOCS_DOCUMENTATION_PREFIX', PONYDOCS_DOCUMENTATION_NAMESPACE_NAME . ':' );

pdefine('PONYDOCS_DOCUMENTATION_PRODUCTS_TITLE', PONYDOCS_DOCUMENTATION_PREFIX . 'Products' );
pdefine('PONYDOCS_PRODUCT_LEGALCHARS', 'A-Za-z0-9_,.-' );
pdefine('PONYDOCS_PRODUCT_REGEX', '/([' . PONYDOCS_PRODUCT_LEGALCHARS . ']+)/' );
pdefine('PONYDOCS_PRODUCT_STATIC_PREFIX', '.');

pdefine('PONYDOCS_PRODUCTVERSION_SUFFIX', ':Versions' );
pdefine('PONYDOCS_PRODUCTVERSION_LEGALCHARS', 'A-Za-z0-9_,.-' );
pdefine('PONYDOCS_PRODUCTVERSION_REGEX', '/([' . PONYDOCS_PRODUCTVERSION_LEGALCHARS . ']+)/' );
pdefine('PONYDOCS_PRODUCTVERSION_TITLE_REGEX', '/^' . PONYDOCS_DOCUMENTATION_PREFIX . '([' . PONYDOCS_PRODUCT_LEGALCHARS . ']+)' . PONYDOCS_PRODUCTVERSION_SUFFIX . '/' );

pdefine('PONYDOCS_PRODUCTMANUAL_SUFFIX', ':Manuals' );
pdefine('PONYDOCS_PRODUCTMANUAL_LEGALCHARS', 'A-Za-z0-9_,.-' );
pdefine('PONYDOCS_PRODUCTMANUAL_REGEX', '/([' . PONYDOCS_PRODUCTMANUAL_LEGALCHARS . ']+)/' );
pdefine('PONYDOCS_PRODUCTMANUAL_TITLE_REGEX', '/^' . PONYDOCS_DOCUMENTATION_PREFIX . '([' . PONYDOCS_PRODUCT_LEGALCHARS . ']+)' . PONYDOCS_PRODUCTMANUAL_SUFFIX . ':([a-zA-Z]{2})/' );

// category cache expiration in seconds
pdefine('CATEGORY_CACHE_TTL', 300);
pdefine('PONYDOCS_TEMP_DIR', '/tmp/');

// Static Documentation
pdefine('PONYDOCS_STATIC_DIR', '/var/www/useruploads/docs/staticDocs');
pdefine('PONYDOCS_STATIC_PATH', 'DocumentationStatic');
pdefine('PONYDOCS_STATIC_URI', '/' . PONYDOCS_STATIC_PATH . '/');

// specify URI to CSS file to dynamically override static documentation iframe CSS
pdefine('PONYDOCS_STATIC_CSS', '');

// capitalization settings
pdefine('PONYDOCS_CASE_SENSITIVE_TITLES', false);

// pdf output constants
$uname = php_uname();
if (preg_match('/Darwin/i', $uname) && preg_match('/x86_64/i', $uname))
	$wkhtmlbin = 'wkhtmltopdf-osx64';
else if (preg_match('/x86_64/i', $uname))
	$wkhtmlbin = 'wkhtmltopdf-amd64';
else
	$wkhtmlbin = 'wkhtmltopdf-i386';
pdefine('PONYDOCS_WKHTMLTOPDF_PATH', dirname(__FILE__) . "/bin/{$wkhtmlbin}");

