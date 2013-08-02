<?php

/**
 * PonyDocs Extension Configuration
 * 
 * You really should not need to edit anything here!
 * 
 * Seriously!
 * 
 * 
 */

// Define your user groups here
define('PONYDOCS_EMPLOYEE_GROUP', 'employees');
define('PONYDOCS_BASE_AUTHOR_GROUP', 'docteam');
define('PONYDOCS_BASE_PREVIEW_GROUP', 'preview');
define('PONYDOCS_CRAWLER_AGENT_REGEX', '/gsa-crawler/');

// Define Namespace information
define('PONYDOCS_DOCUMENTATION_NAMESPACE_NAME', 'Documentation');
define('PONYDOCS_DOCUMENTATION_NAMESPACE_ID', 100);

define('PONYDOCS_LANGUAGE_DEFAULT', 'en'); 
define('PONYDOCS_LANGUAGE_AUTOUI', true);

define('PONYDOCS_CACHE_ENABLED', false);
define('PONYDOCS_CACHE_DEBUG', false);
define('PONYDOCS_REDIRECT_DEBUG', true);
define('PONYDOCS_SESSION_DEBUG', false);
define('PONYDOCS_AUTOCREATE_DEBUG', false);
define('PONYDOCS_CASE_INSENSITIVE_DEBUG', false);
define('PONYDOCS_DOCLINKS_DEBUG', false);

define('PONYDOCS_DOCUMENTATION_PREFIX', PONYDOCS_DOCUMENTATION_NAMESPACE_NAME . ':' );

define('PONYDOCS_DOCUMENTATION_PRODUCTS_TITLE', PONYDOCS_DOCUMENTATION_PREFIX . 'Products' );
define('PONYDOCS_PRODUCT_LEGALCHARS', 'A-Za-z0-9_,.-' );
define('PONYDOCS_PRODUCT_REGEX', '/([' . PONYDOCS_PRODUCT_LEGALCHARS . ']+)/' );
define('PONYDOCS_PRODUCT_STATIC_PREFIX', '.');

define('PONYDOCS_PRODUCTVERSION_SUFFIX', ':Versions' );
define('PONYDOCS_PRODUCTVERSION_LEGALCHARS', 'A-Za-z0-9_,.-' );
define('PONYDOCS_PRODUCTVERSION_REGEX', '/([' . PONYDOCS_PRODUCTVERSION_LEGALCHARS . ']+)/' );
define('PONYDOCS_PRODUCTVERSION_TITLE_REGEX', '/^' . PONYDOCS_DOCUMENTATION_PREFIX . '([' . PONYDOCS_PRODUCT_LEGALCHARS . ']+)' . PONYDOCS_PRODUCTVERSION_SUFFIX . '/' );

define('PONYDOCS_PRODUCTMANUAL_SUFFIX', ':Manuals' );
define('PONYDOCS_PRODUCTMANUAL_LEGALCHARS', 'A-Za-z0-9_,.-' );
define('PONYDOCS_PRODUCTMANUAL_REGEX', '/([' . PONYDOCS_PRODUCTMANUAL_LEGALCHARS . ']+)/' );
define('PONYDOCS_PRODUCTMANUAL_TITLE_REGEX', '/^' . PONYDOCS_DOCUMENTATION_PREFIX . '([' . PONYDOCS_PRODUCT_LEGALCHARS . ']+)' . PONYDOCS_PRODUCTMANUAL_SUFFIX . '/' );

// category cache expiration in seconds
define('CATEGORY_CACHE_TTL', 300);
define('PONYDOCS_TEMP_DIR', '/tmp/');

// Static Documentation
define('PONYDOCS_STATIC_DIR', '/var/www/useruploads/docs/staticDocs');
define('PONYDOCS_STATIC_PATH', 'DocumentationStatic');
define('PONYDOCS_STATIC_URI', '/' . PONYDOCS_STATIC_PATH . '/');

// specify URI to CSS file to dynamically override static documentation iframe CSS
define('PONYDOCS_STATIC_CSS', '');

// capitalization settings
define('PONYDOCS_CASE_SENSITIVE_TITLES', false);

// pdf output constants
$uname = php_uname();
if (preg_match('/Darwin/i', $uname) && preg_match('/x86_64/i', $uname))
	$wkhtmlbin = 'wkhtmltopdf-osx64';
else if (preg_match('/x86_64/i', $uname))
	$wkhtmlbin = 'wkhtmltopdf-amd64';
else
	$wkhtmlbin = 'wkhtmltopdf-i386';

define('PONYDOCS_WKHTMLTOPDF_PATH', dirname(__FILE__) . "/bin/{$wkhtmlbin}");

