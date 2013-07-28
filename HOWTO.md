# How To Guide

## Create your products on the front-end.

1. Log in as your administrator user and visit the Documentation:Products page (this should happen automatically for you)
	* If MediaWiki was installed at the base of your documentation root, then simply go to /Documentation:Products.
2. (only needed if redirect was not done automatically) Click on the "Create" tab at the top of the page to edit the page and add new products.
3. Follow the instructions at the top of the page

### The Documentation:Products page contains a listing of all your products.

* Each line defines a single product.
  `{{#product:productShortName|displayName|description|parent}}`
	* productShortName can be any alphanumeric string (no spaces allowed).
	* displayName is the human readable name of the product you are documenting.
	* description is a short description of the product
	* parent is the short name of a parent product.
	* Child products will be displayed under their parents in product listings.
* Here's an example of a page with the three products above:
	```
	{{#product:Foo|Foo for fooing|Foo is the synergy of three popular domain-specific languages|}}
	{{#product:Bar|Bar for the bar|You've never seen a Bar like this before|}}
	{{#product:Bash|Bash is not Quux|Bash is a Quux-like framework for rapid prototyping|Bar}}
	```
* Only productShortName is required. displayName will default to shortName if left empty. 
* Please do include all the pipes if you are leaving some variables empty:
  `{{#product:Quux|||}}`
* Once the page is saved, you'll be able to move to the next step, defining your versions.
* As you add more products, add more lines to the Documentation:Products page.
* Don't forget to add corresponding elements to the `$ponyDocsProductsList` array in LocalSettings.php, as
  documented in Step 5 above.

## Create your first product version.

1. Logged in as your administrator user, visit the Documentation:productShortName:Versions page. (you can do this via the Admin Menu)
	* productShortName is the shortName of one of the products defined above
	* If MediaWiki was installed at the base of your documentation root, then simply go to
	  /Documentation:productShortName:Versions.
2. Click on the "Create" tab at the top of the page to edit the page and add new versions.

### The Documentation:productShortName:Versions page contains a listing of all versions of a product and their status

* The status can be "released", "unreleased" or "preview".
* For regular users, only "released" versions can be seen.
* For employee and productShortName-docteam users, all versions can be seen.
* There is also a productShortName-preview group which can see preview versions for that product.
* Each line in Documentation:productShortName:Versions must use the following syntax to define a version:
  `{{#version:versionName|status}}`
* versionName can be any alphanumeric string (no spaces or underscores allowed).
	* versionName should match your software's version. Status is either "released", "unreleased" or  "preview".
	* For example, to initialize version 1.0 of your product, have the following line in your 
	  Documentation:productShortName:Versions page:
	  `{{#version:1.0|unreleased}}`
* Once the Documentation:productShortName:Versions page is saved, you'll be able to move to the next step, defining your first
  manual.
* As you add more versions of your product, add more lines to the Documentation:productShortName:Versions page.

# Create your first manual.

1. Now head to /Documentation:productShortName:Manuals. (do this via the Admin Menu)
2. Click on the "Create" tab at the top of the page to edit the page and add new manuals.

### The Documentation:productShortName:Manuals page defines the Manuals available for a product version.

* A version can have all the manuals, or a sub-set of the manuals you define here.
* You'll create the links of the manuals to your first version in the next step.
* For now, you'll need to define the first manual.
* Each line in Documentation:productShortName:Manuals must use the following syntax to define a manual:
  `{{#manual:manualShortName|displayName}}`
* manualShortName can be any alphanumeric string (no spaces allowed).
	* For example, "Installation".
* displayName is the human readable name of the manual.
	* displayName can have spaces and is the full name of the Manual.
	* For example, "Installation Manual".
* The following lines create two manuals called Installation and FAQ:
	```
	{{#manual:Installation|Installation Manual}}
	{{#manual:FAQ|FAQ}}
	```
* Once saved, you will see the listing of your manuals.
* Each manual name will be a link to create the Table of Contents for your current version (in this case, the first version you
  created in Documentation:productShortName:Versions).
* By clicking on the Manual name, you'll proceed to the next step. 

## Create your first Table of Contents (TOC) and auto-generate your first topic.

Admin Menu > PRODUCT > VERSION > Manage Table of Contents (for a Manual)

   Use the following syntax to create the TOC for this manual:
	```
	Section Header
	* {{#topic:Topic Title}}
	```
   For example:
	```
	Getting Started
	* {{#topic:Before You Begin}}
	* {{#topic:Next Steps}}
	Common problems
	* {{#topic:Disk Permissions}}
	* {{#topic:Database Errors}}

	[[Category:V:productShortName:1.0]]
	```

* Note the use of the Category tag inside the TOC, which should have been auto-populated when the page was created.
	* This will ensure the TOC is linked to version 1.0.
* You must have this category tag present in order for the TOC to properly render for that version.
* You must have at least one Section Header before the first topic tag, and all topic tags must be unordered list items.
* When you save the edit to your first TOC page, links to your new topics will automatically be created.
* Clicking on the topic in the TOC page will take you to the new topic, which you'll be able to edit with your new content.
* Note that each new topic page is also auto-populated with a category tag (or tags).

## wkhtmltopdf

This is already packaged with the product, but if you are having problems refer to the rest of this section.

Thank you to PonyDocs extension there is a PDF Version functionality. 

* If you would like this to work, you'll need to install wkhtmltopdf. It can be downloaded here:
  https://code.google.com/p/wkhtmltopdf/downloads/list
* We recommend putting the binary in extensions/Ponydocs/bin/. Make sure it's executable by the web server user.
* PonyDocs has only been tested with wkhtmltopdf version 0.10.0 rc2
* Additionally, you'll need to make sure your MEDIAWIKIBASE/images directory is writable by your web server user.

This should get you started! Have fun!
