<?php

################# PONYDOCS START #################
// Implicit group for all visitors, remove access beyond reading
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['upload'] = false;
$wgGroupPermissions['*']['reupload'] = false;
$wgGroupPermissions['*']['reupload-shared'] = false;
$wgGroupPermissions['*']['writeapi'] = false;
$wgGroupPermissions['*']['createtalk'] = false;
$wgGroupPermissions['*']['read'] = true;

// User is logged-in. Ensure that they still can't edit.
$wgGroupPermissions['user']['read'] = true;
$wgGroupPermissions['user']['createtalk'] = false;
$wgGroupPermissions['user']['upload'] = false;
$wgGroupPermissions['user']['reupload'] = false;
$wgGroupPermissions['user']['reupload-shared'] = false;
$wgGroupPermissions['user']['edit'] = false;
$wgGroupPermissions['user']['move'] = false;
$wgGroupPermissions['user']['minoredit'] = false;
$wgGroupPermissions['user']['createpage'] = false;
$wgGroupPermissions['user']['writeapi'] = false;
$wgGroupPermissions['user']['move-subpages'] = false;
$wgGroupPermissions['user']['move-rootuserpages'] = false;
$wgGroupPermissions['user']['purge'] = false;
$wgGroupPermissions['user']['sendemail'] = false;
$wgGroupPermissions['user']['writeapi'] = false;

// Our "in charge" group.
$wgGroupPermissions['bureaucrat']['userrights'] = true;
// Custom permission to branch ALL topics for a version.
$wgGroupPermissions['bureaucrat']['branchall'] = true;

// Implicit group for accounts that pass $wgAutoConfirmAge
$wgGroupPermissions['autoconfirmed']['autoconfirmed'] = true;

// Implicit group for accounts with confirmed email addresses
// This has little use when email address confirmation is off
$wgGroupPermissions['emailconfirmed']['emailconfirmed'] = true;

// Users with bot privilege can have their edits hidden from various log pages by default
$wgGroupPermissions['bot']['bot'] = true;
$wgGroupPermissions['bot']['autoconfirmed'] = true;
$wgGroupPermissions['bot']['nominornewtalk'] = true;
$wgGroupPermissions['bot']['autopatrol'] = true;

$wgArticlePath = '/$1';

// Ponydocs environment configuration.  update to your
// specific install
define('PONYDOCS_PRODUCT_LOGO_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/extensions/PonyDocs/images/pony.png');
define('PONYDOCS_PDF_COPYRIGHT_MESSAGE', 'Copyright Your Company, Inc. All Rights Reserved');
define('PONYDOCS_PDF_TITLE_IMAGE_PATH', '/extensions/PonyDocs/images/pony.png');
define('PONYDOCS_DEFAULT_PRODUCT', 'Example');
define('PONYDOCS_ENABLE_BRANCHINHERIT_EMAIL', true);


include_once($IP . "/extensions/PonyDocs/PonyDocsExtension.php");
#################  PONYDOCS END #################

?>