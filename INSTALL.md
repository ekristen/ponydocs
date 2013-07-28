WikiDocs 1.0 Beta RC1 
=====================

Open Source documentation based on MediaWiki + PonyDocs

To borrow a term from Splunk -- Splunk > Open Source FTW!

Prerequisites & Assumptions
---------------------------

1. You're a sysadmin
2. You have a place to host a full MediaWiki system.
3. You are running Apache (other http servers are potentially usable, no testing has been done)
4. You can use mod_rewrite in your Apache configuration
5. You know this is Beta ;) (stolen shamelessly from PonyDocs)
6. You can follow a GUI based install process.

Further PonyDocs Assumptions
----------------------------

It is further assumed that have 4 classes of users of your wiki:

* Anonymous and guests who are logged in
	* These are folks who fall into the (default) or "user" group. 
	* They can *only* read and can not edit any pages.
* Employees
	* Folks who are in the "Employee" group and can edit any single page but not use any advanced PonyDocs functions like creating
      TOCs, Versions and Branching or Inheriting
* Editors
	* Folks who can do it all, short of editing user perms.
	* They are in the "PRODUCT-docteam" group.
	* There is a per product docteam group so if you had a product called "Foo", the editor would need  to be in the "Foo-docteam"
	  group
* Admins
	* Folks who can add, remove, and move Employees and Editors to the different product docteam groups

Quick Install Instructions
--------------------------

1. git clone https://github.com/ekristen/wikidocs.git
2. Configure apache to load the WikiDocs folder
3. Browse to your WikiDocs setup and follow the on screen instructions

Post Install Configuration
--------------------------

WARNING: Modifying defaults beyond what is listed here has not been tested and make BREAK your installation.

Default PonyDocs settings are located in extensions/PonyDocs/PonyDocs.LocalSettings.php (this differs from the original PonyDocs project)

1. Set `$wgLogo` to the PonyDocs logo if you like!
2. Modify your `$wgGroupPermissions` to add PonyDoc's additional permissions to your existing groups.
	* These permissions are named are branchtopic, branchmanual, inherit, viewall.
	* You can also create new groups for your permissions.
	* Review [Manual:User_rights](http://www.mediawiki.org/wiki/Manual:User_rights) for more information.  
3. Make sure to define $wgArticlePath (some MediaWiki instances do not have this property defined.)
	* Refer to [Manual:$wgArticlePath](http://www.mediawiki.org/wiki/Manual:$wgArticlePath) for more information.  
   For example: if MediaWiki was installed at the root of your html directory:
   `$wgArticlePath = '/$1';`
4. Update all the PONYDOCS_ contents to fit to your installation.
	* `PONYDOCS_PRODUCT_LOGO_URL`
	* `PONYDOCS_PDF_COPYRIGHT_MESSAGE`
	* `PONYDOCS_PDF_TITLE_IMAGE_PATH`
	* `PONYDOCS_DEFAULT_PRODUCT`
	* `PONYDOCS_ENABLE_BRANCHINHERIT_EMAIL`

5. Have a looksee at PonyDocsConfig.php

* Take a look at extensions/Ponydocs/PonyDocs.config.php.
* It will define a bunch of constants, most of which you shouldn't need to touch.
* As of this writing, changing these values has not been tested.

