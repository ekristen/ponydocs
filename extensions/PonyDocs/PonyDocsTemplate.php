<?php

class PonyDocsSkinTemplate extends SkinTemplate {
	// We are going to totally overwrite this functionality to fix a weird issue
	public function setTitle($t) {
		global $wgTitle;
		$this->mTitle = $wgTitle;
	}

	function printSource() {
		return '';
	}
	
}



/**
 * PonyDocsTemplate Class
 * 
 * 
 * @author Erik Kristensen
 */
class PonyDocsTemplate extends QuickTemplate {
	var $skin;

	var $inDocumentation = false;

	var $template = 'default';

	var $templateDirectory = 'templates';

	var $_pageTitle = 'WikiDocs';

	var $skin_names = array();

	/**
	 * This lets you map full titles or namespaces to specific PHP template files and prep methods.  The special '0' index
	 * is the default if not found.  Prefix title mappings with 'T:' and namespace mappings with 'NS:'.  Currently if inside
	 * a namespace it will ignore any title mappings (i.e., either it calls the NS:namespace or the default).
	 *
	 * @var array
	 */
	private $_methodMappings = array();

	function __construct() {
		parent::__construct();

		$this->_methodMappings = array(
			0 => array( 'init' => '', 'tpl' => 'default.tpl.php' ),
			// Any title that is actually a page and in the PONYDOCS namespace
			'NS:'.PONYDOCS_DOCUMENTATION_NAMESPACE_NAME => array( 'init' => 'prepareDocumentation', 'tpl' => 'documentation.tpl.php' ),
			// Matches /Documentation page
			'/^T:(([a-zA-Z]{2})\/)?'.PONYDOCS_DOCUMENTATION_NAMESPACE_NAME."(\/)?$/" => array( 'init' => 'prepareDocumentation', 'tpl' => 'home.tpl.php' ),
			// Matches /Documentation/PRODUCT page
			"/^T:(([a-zA-Z]{2})\/)?".PONYDOCS_DOCUMENTATION_NAMESPACE_NAME."\/([".PONYDOCS_PRODUCT_LEGALCHARS."]+)(\/)?$/" => array('init' => 'prepareDocumentation', 'tpl' => 'product.tpl.php'),
			// Matches /Documentation/PRODUCT/VERSION page
			"/^T:(([a-zA-Z]{2})\/)?".PONYDOCS_DOCUMENTATION_NAMESPACE_NAME."\/([".PONYDOCS_PRODUCT_LEGALCHARS."]+)(\/([".PONYDOCS_PRODUCTVERSION_LEGALCHARS."]+))?$/" => array('init' => 'prepareDocumentation', 'tpl' => 'product.tpl.php')
		);

		// Add Skins in reverse order of calling
		// array_unshift($this->skin_names, str_replace('Template', '', get_class()));
		$aClasses = get_declared_classes();
		$aChildrenOf = array();
		$rcParentClass = new ReflectionClass('PonyDocsTemplate');
		foreach ($aClasses AS $class) {
			$rcCurClass = new ReflectionClass($class);
			if ($rcCurClass->isSubclassOf($rcParentClass)) {
				array_unshift($this->skin_names, str_replace('Template', '', $rcCurClass->name));
			}
		}
	}



	/**
	 * This sets up our PonyDocs Template
	 * 
	 */
	function preSetup() {
		global $wgUser, $wgExtraNamespaces, $wgTitle, $wgArticlePath, $IP;
		global $wgRequest, $wgRevision, $action, $wgRequest, $wgSitename;
		global $wgPonyDocsLanguage;

		$this->globals = new stdClass();
		$this->globals->wgRequest = $wgRequest;
		$this->globals->wgUser = $wgUser;
		$this->globals->wgTitle = $wgTitle;
		$this->globals->wgArticlePath = $wgArticlePath;
		$this->globals->wgRevision = $wgRevision;
		$this->globals->wgRequest = $wgRequest;
		$this->globals->wgSitename = $wgSitename;
		$this->globals->wgExtraNamespaces = $wgExtraNamespaces;
		$this->globals->IP = $IP;

		$this->action = $action;

		PonyDocsProduct::LoadProducts(false, $wgPonyDocsLanguage);
		$this->data['selectedProduct'] = PonyDocsProduct::GetSelectedProduct( $wgPonyDocsLanguage );
		PonyDocsProductVersion::LoadVersionsForProduct($this->data['selectedProduct']);
		PonyDocsProductManual::LoadManualsForProduct($this->data['selectedProduct'], false, $wgPonyDocsLanguage );

		$this->ponydocs = $ponydocs = PonyDocsWiki::getInstance( $this->data['selectedProduct'] );

		$this->data['products'] = $ponydocs->getProductsForTemplate( );
		$this->data['versions'] = $ponydocs->getVersionsForProduct( $this->data['selectedProduct'] );
		$this->data['namespaces'] = $wgExtraNamespaces;
		$this->data['selectedVersion'] = PonyDocsProductVersion::GetSelectedVersion( $this->data['selectedProduct'] );
		$this->data['pVersion'] = PonyDocsProductVersion::GetVersionByName($this->data['selectedProduct'], $this->data['selectedVersion'], $wgPonyDocsLanguage);
		$this->data['selectedLanguage'] = $wgPonyDocsLanguage;

		// TODO: FIX -- Disabled due to wgScript and thispage not being set?
		//$this->data['versionurl'] = $this->data['wgScript'] . '?title=' . $this->data['thispage'] . '&action=changeversion';

		if (PONYDOCS_SESSION_DEBUG) {
			error_log("DEBUG [" . __METHOD__ . "] selected product/version is set to " . $this->data['selectedProduct'] . "/" . $this->data['selectedVersion']);
		}
/*
		$this->_methodMappings["/^T:(([a-zA-Z]{2})\/)?".PONYDOCS_DOCUMENTATION_NAMESPACE_NAME."\/([".PONYDOCS_PRODUCT_LEGALCHARS."]+)(\/([".PONYDOCS_PRODUCTVERSION_LEGALCHARS."]))?$/"] = array('init' => 'prepareDocumentation', 'tpl' => 'product.tpl.php');

		foreach (PonyDocsProduct::GetDefinedProducts($this->data['selectedLanguage']) as $product) {
			$this->_methodMappings["T:Documentation/{$product->getShortName()}"] = array('init' => 'prepareDocumentation', 'tpl' => 'product.tpl.php');

			foreach (PonyDocsProductVersion::GetVersions($product->getShortName()) as $version) {
				$this->_methodMappings["T:Documentation/{$product->getShortName()}/{$version->getVersionName()}"] = array('init' => 'prepareDocumentation', 'tpl' => 'product.tpl.php');
			}
		}
*/

		if($this->data['nscanonical'] == PONYDOCS_DOCUMENTATION_NAMESPACE_NAME
		|| $wgTitle->__toString() == PONYDOCS_DOCUMENTATION_NAMESPACE_NAME
		|| preg_match('/^' . PONYDOCS_DOCUMENTATION_NAMESPACE_NAME . '/', $wgTitle->__toString())
		|| preg_match('/^(([a-zA-Z]{2})\/)?'.PONYDOCS_DOCUMENTATION_NAMESPACE_NAME.'/', $wgTitle->__toString())
		) {
			$this->inDocumentation = true;
		}

		$this->adminMenu = $this->createAdminMenu();
		$this->navigationMenu = $this->createNavigationMenu();
		$this->breadcrumbMenu = $this->createBreadcrumbMenu();

	} // end function __construct

	/**
	 * This is the main execute function that gets called 
	 * from the QuickTemplate OutputPage function.
	 */
	function execute() {
		global $wgOut;

		$this->preSetup();

		$this->skin = $skin = $this->data['skin'];

		if($this->skin->mTitle) {
			$this->data['canonicalURI'] = $this->skin->mTitle->getFullURL();
		}

		/**
		 * When displaying a page we output header.php, then a sub-template, and then footer.php.  The namespace
		 * which we are in determines the sub-template, which is named 'ns<Namespace>'.  It defaults to our
		 * nsDefault.php template. 
		 */
		$this->template = $this->_methodMappings[0];

		$idx = $this->data['nscanonical'] ? 'NS:'.$this->data['nscanonical'] : 'T:' . $this->globals->wgTitle->__toString( );

		foreach ($this->_methodMappings as $regex => $mappings) {
			if (preg_match($regex, $idx, $match)) {
				$this->template = $mappings;
				break;
			}

			if ($idx === $regex) {
				$this->template = $mappings;
				break;
			}
		}

		// Call our init function if it exists.
		if (method_exists($this, $this->template['init'])) {
			call_user_func(array(&$this, $this->template['init']));
		}

		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

		foreach ($this->skin_names as $skin_name) {
			$template_files[] = "{$this->globals->IP}/skins/{$skin_name}/templates/{$this->template['tpl']}";
		}

		$found = false;
		for ($x=0; $x<count($template_files); $x++) {
			if (is_file($template_files[$x]) && is_readable($template_files[$x])) {
				$found = true;
				$template_file = $template_files[$x];
			}
		}

		if ($found == false) {
			throw new Exception("Unable to find template file: {$template_files[$x]}");
		}

		ob_start();
		require("{$template_file}");
		$content = ob_get_clean();
		print $content;

		wfRestoreWarnings();
	} // end of execute() method


	public function prepareDocumentation() {
		global $wgArticle, $wgParser, $wgTitle, $wgOut, $wgScriptPath, $wgUser, $wgPonyDocsLanguage;

		/**
		 * We need a lot of stuff from our PonyDocs extension!
		 */
		$ponydocs = PonyDocsWiki::getInstance( $this->data['selectedProduct'], $wgPonyDocsLanguage );
		$this->data['manuals'] = $ponydocs->getManualsForProduct( $this->data['selectedProduct'] , $wgPonyDocsLanguage );

		/**
		 * Adjust content actions as needed, such as add 'view all' link.
		 */

		if ( !strcmp( PONYDOCS_DOCUMENTATION_NAMESPACE_NAME . '/' . $this->data['selectedProduct'], $wgTitle->__toString()) ||
			 !strcmp( PONYDOCS_DOCUMENTATION_NAMESPACE_NAME . '/' . $this->data['selectedProduct'] . '/' . $this->data['selectedVersion'], $wgTitle->__toString()) ||
			 !strcmp( PONYDOCS_DOCUMENTATION_NAMESPACE_NAME . '/' . $this->data['selectedProduct'] . '/latest', $wgTitle->__toString())) {
			$this->data['titletext'] = $this->data['selectedProductLabel'];
			$this->data['show_versions'] = true;
		}

		/**
		 * Possible topic syntax we must handle:
		 * 
		 * Documentation:<topic> *Which may include a version tag at the end, we don't care about this.
		 * Documentation:<productShortName>:<manualShortName>:<topic>:<version>
		 * Documentation:<productShortName>:<manualShortName>
		 */

		/**
		 * Based on the name; i.e. 'Documentation:Product:Manual:Topic' we need to parse it out and store the manual name and
		 * the topic name as parameters.  We store manual in 'manualname' and topic in 'topicname'.  Special handling
		 * needs to be done for versions and TOC?
		 *
		 * 	0=NS (Documentation)
		 *  1=Product (Short name)
		 *  2=Manual (Short name)
		 *  3=Topic
		 *  4=Version
		 */
		$pManual = null;
		$pieces = explode( ':', $wgTitle->__toString( ));
		$helpClass = '';

		/**
		 * This isn't a specific topic+version -- handle appropriately.
		 */
		if( sizeof( $pieces ) < 5 )
		{
			if( !strcmp( PONYDOCS_DOCUMENTATION_PREFIX . $this->data['selectedProduct'] . PONYDOCS_PRODUCTVERSION_SUFFIX . ":$wgPonyDocsLanguage" , $wgTitle->__toString( )))
			{
				$this->data['titletext'] = 'Versions Management - '.$this->data['selectedProduct'];
				$this->data['headertext'] = $this->data['titletext'];
				$wgOut->prependHTML( '<div class="alert alert-success' . $helpClass . '"><strong>Instructions:</strong><br><i>* Use <strong>{{#version:name|status}}</strong> to define a new version, where status is <strong>released</strong>, <strong>unreleased</strong>, or <strong>preview</strong>.  Valid chars in version name are A-Z, 0-9, period, comma, and dash.</i></div>');
			}
			else if( !strcmp( PONYDOCS_DOCUMENTATION_PREFIX . $this->data['selectedProduct'] . PONYDOCS_PRODUCTMANUAL_SUFFIX. ":{$this->data['selectedLanguage']}", $wgTitle->__toString( )))
			{
				$this->data['titletext'] = 'Manuals Management - '.$this->data['selectedProduct'];
				$this->data['headertext'] = $this->data['titletext'];
				$wgOut->prependHTML( '<div class="alert alert-success' . $helpClass . '"><strong>Instructions:</strong><br><i>* Use <strong>{{#manual:manualShortName|displayName|Description}}</strong> to define a new manual.  If you omit display name, the short name will be used in links.</i></div>');
			}
			else if ( !strcmp( PONYDOCS_DOCUMENTATION_PRODUCTS_TITLE . ":{$this->data['selectedLanguage']}", $wgTitle->__toString( )))
			{
				$this->data['titletext'] = 'Products Management';
				$this->data['headertext'] = $this->data['titletext'];
				$wgOut->prependHTML("</div>");
				$wgOut->prependHTML('<br><span class="' . $helpClass . '"><i>* The first product listed here will be the DEFAULT product for WikiDocs.</span>');
				$wgOut->prependHTML('<br><span class="' . $helpClass . '"><i>* The order on this page dictates the order the products will appear elsewhere.</span>');
				$wgOut->prependHTML('<strong>Warning</strong>');
				$wgOut->prependHTML('<div class="alert alert-warning">');
				$wgOut->prependHTML('</div>');
				$wgOut->prependHTML('<br><span class="' . $helpClass . '"><i>* If you leave displayName empty, productShortName will be used in links.</i></span>');
				$wgOut->prependHTML('<br><span class="' . $helpClass . '"><i>* parent can be left empty. Example: {{#product:test|Test|Test|}} (note the final "|")</i></span>');
				$wgOut->prependHTML('<br><span class="' . $helpClass . '"><i>* Use {{#product:productShortName|displayName|description|parent}} to define a new product.</i></span>');
				$wgOut->prependHTML('<strong>Instructions</strong>');
				$wgOut->prependHTML('<div class="alert alert-success">');
			}
			else if (isset($pieces[2]) && preg_match( '/(.*)TOC(.*)/', $pieces[2], $matches ))
			{
				$this->data['selectedManual'] = $matches[1];
				$this->data['titletext'] = $matches[1] . ' Table of Contents Page';
				$this->data['headertext'] = $this->data['titletext'];

				$wgOut->prependHTML( '</div>' );
				$wgOut->prependHTML( '<br/><span class="' . $helpClass . '"><i>* Use {{#topic:Display Name}} within a bullet to create topics.</i></span>' );
				$wgOut->prependHTML( '<br/><span class="' . $helpClass . '"><i>* Topic bullets must be preceded by at least one section name in plain text.</i></span>' );
				$wgOut->prependHTML( '<strong>Instructions</strong>' );
				$wgOut->prependHTML( '<div class="alert alert-success">');
			}
			else if ( count( $pieces ) >= 4 && PonyDocsProductManual::IsManual( $pieces[1], $pieces[2] ))
			{
				$pManual = PonyDocsProductManual::GetManualByShortName( $pieces[1], $pieces[2] );
				$this->data['selectedManual'] = $pieces[2];
				if( $pManual )
					$this->data['manualname'] = $pManual->getLongName( );
				else
					$this->data['manualname'] = $pieces[2]; 
				$this->data['topicname'] = $pieces[3];
				$this->data['titletext'] = '';
				$this->data['headertext'] = $this->data['titletext'];
			}
			else
			{
				$subpieces = explode("/", $wgTitle->__toString( ));
				if (count($subpieces) >= 2) {
					if ($subpieces[0] == PONYDOCS_DOCUMENTATION_NAMESPACE_NAME) {
						$subprod = $subpieces[1];
					}
					else if ($subpieces[1] == PONYDOCS_DOCUMENTATION_NAMESPACE_NAME) {
						$subprod = $subpieces[2];
					}

					$product = PonyDocsProduct::GetProductByShortName($subprod, $wgPonyDocsLanguage);
					$this->data['headertext'] = $product->getLongName() . ' <small>Manuals</small>';
				}
				else {
					$this->data['topicname'] = $pieces[0];
					$this->data['headertext'] = $pieces[0];
				}
			}
		}
		else
		{
			$product = PonyDocsProduct::GetProductByShortName(PonyDocsProduct::GetSelectedProduct());
			$pManual = PonyDocsProductManual::GetManualByShortName( $pieces[1], $pieces[2], $pieces[5] );
			$this->data['selectedManual'] = $pieces[2];
			if( $pManual )
				$this->data['manualname'] = $pManual->getLongName( );
			else
				$this->data['manualname'] = $pieces[2];
			$this->data['topicname'] = $pieces[3];

			$h1 = PonyDocsTopic::FindH1ForTitle( $wgTitle->__toString( ));
			if( $h1 !== false )
				$this->data['titletext'] = $h1;

			$this->data['headertext'] = "{$product->getLongName()} <small>{$this->data['manualname']}</small>";

			global $action;
			if ($action == 'edit') {
				$wgOut->prependHTML('<p>NOTICE: Do not remove the Category Links!!!</p>');
			}
		}

		/**
		 * Get current topic, passing it our global Article object.  From this, generate our TOC based on the current
		 * topic selected.  This generates our left sidebar TOC plus our prev/next/start navigation links.  This should ONLY
		 * be done if we actually are WITHIN a manual, so special pages like TOC, etc. should not do this!
		 */

		if( $pManual )
		{
			$p = PonyDocsProduct::GetProductByShortName( $this->data['selectedProduct'] );
			$v = PonyDocsProductVersion::GetVersionByName( $this->data['selectedProduct'], $this->data['selectedVersion'] );
			$toc = new PonyDocsTOC( $pManual, $v, $p, $this->data['selectedLanguage'] );
			list( $this->data['manualtoc'], $this->data['tocprev'], $this->data['tocnext'], $this->data['tocstart'] ) = $toc->loadContent( );
			$this->data['toctitle'] = $toc->getTOCPageTitle();
			$this->data['toctranslations'] = $toc->getTranslations();
		}

		/**
		 * Create a PonyDocsTopic from our article.  From this we populate:
		 *
		 * topicversions:  List of version names topic is tagged with.
		 * inlinetoc:  Inline TOC shown above article body.
		 * catcode:  Special category code.
		 * cattext:  Category description.
		 * basetopicname:  Base topic name (w/o :<version> at end).
		 * basetopiclink:  Link to special TopicList page to view all same topics.
		 */

		//echo '<pre>' ;print_r( $wgArticle ); die();
		$topic = new PonyDocsTopic( $wgArticle );

		if( preg_match( '/^' . PONYDOCS_DOCUMENTATION_PREFIX . '(.*):(.*):(.*):(.*)/', $wgTitle->__toString( )) ||
			preg_match( '/^' . PONYDOCS_DOCUMENTATION_PREFIX . '.*:.*TOC.*/', $wgTitle->__toString( )))
		{
			$this->data['topicversions'] = PonyDocsWiki::getVersionsForTopic( $topic );
			$this->data['inlinetoc'] = $topic->getSubContents( );
			$this->data['versionclass'] = $topic->getVersionClass( );
			$this->data['versionGroupMessage'] = $this->data['pVersion']->getVersionGroupMessage();
			$this->data['topictranslations'] = $topic->getTranslations();

			/**
			 * Sort of a hack -- we only use this right now when loading a TOC page which is new/does not exist.  When this
			 * happens a hook (AlternateEdit) adds an inline script to define this JS function, which populates the edit
			 * box with the proper Category tag based on the currently selected version.
			 */

			$this->data['body_onload'] = 'ponyDocsOnLoad();';

			switch( $this->data['catcode'] )
			{
				case 0:
					$this->data['cattext'] = 'Applies to latest version which is currently unreleased.';
					break;
				case 1:
					$this->data['cattext'] = 'Applies to latest version.';
					break;
				case 2:
					//$this->data['cattext'] = 'Applies to released version(s) but not the latest.';
					$this->data['cattext'] = 'This documentation does not apply to the most recent version.';
					break;
				case 3:
					$this->data['cattext'] = 'Applies to latest preview version.';
					break;
				case 4:
					$this->data['cattext'] = 'Applies to one or more preview version(s) only.';
					break;
				case 5:	
					$this->data['cattext'] = 'Applies to one or more unreleased version(s) only.';
					break;
				case -2: /** Means its not a a title name which should be checked. */
					break;
				default:
					$this->data['cattext'] = 'Does not apply to any version of PonyDocs.';
					break;
			}
		}

		$this->data['basetopicname'] = $topic->getBaseTopicName( );
		if( strlen( $this->data['basetopicname'] ))
		{
			$this->data['basetopiclink'] = '<a href="' . $wgScriptPath . '/index.php?title=Special:TopicList&topic=' . $this->data['basetopicname'] . '">View All</a>';
		}
		$temp = PonyDocsTopic::FindH1ForTitle(PONYDOCS_DOCUMENTATION_PREFIX . $topic->getTitle()->getText());
		if($temp !== false) {
			// We got an H1!
			$this->data['pagetitle'] = $temp;
		}

		$this->contentActions( );
		$this->navURLS( );
	}


	/**
	 * Update the nav URLs (toolbox) to include certain special pages for authors and bureaucrats.
	 */
	private function navURLS( )
	{
		global $wgUser, $wgArticlePath, $wgArticle, $wgTitle;

		$groups = $wgUser->getGroups( );
		$authProductGroup = PonyDocsExtension::getDerivedGroup();

		if( in_array( 'bureaucrat', $groups ) || in_array( $authProductGroup, $groups ))
		{
			if (isset($this->data['selectedManual'])) {
				$p = PonyDocsProduct::GetProductByShortName( $this->data['selectedProduct'] );
				$m = PonyDocsProductManual::GetManualByShortName($this->data['selectedProduct'], $this->data['selectedManual'], $this->data['selectedLanguage']);
				$v = PonyDocsProductVersion::GetVersionByName( $this->data['selectedProduct'], $this->data['selectedVersion'] );
				$toc = new PonyDocsTOC( $m, $v, $p, $this->data['selectedLanguage'] );
			
				$this->data['nav_urls']['toc_mgmt'] = array(
					'href' => str_replace( '$1', $toc->getTOCPageTitle(), $wgArticlePath ),
					'text' => 'Manage Table of Contents'
				);
			}

			$this->data['nav_urls']['manuals_mgmt'] = array(
				'href' => str_replace( '$1', PONYDOCS_DOCUMENTATION_PREFIX . $this->data['selectedProduct'] . PONYDOCS_PRODUCTMANUAL_SUFFIX . ':' . $this->data['selectedLanguage'], $wgArticlePath),
				'text' => 'Manage Product Manuals'
			);

			$this->data['nav_urls']['versions_mgmt'] = array(
				'href' => str_replace( '$1', PONYDOCS_DOCUMENTATION_PREFIX . $this->data['selectedProduct'] . PONYDOCS_PRODUCTVERSION_SUFFIX, $wgArticlePath),
				'text' => 'Manage Product Versions'
			);

			$this->data['nav_urls']['special_doctopics'] = array(
				'href' => str_replace( '$1', 'Special:DocTopics', $wgArticlePath ),
				'text' => 'Document Topics' );

			$this->data['nav_urls']['special_tocmgmt'] = array(
				'href' => str_replace( '$1', 'Special:TOCList', $wgArticlePath ),
				'text' => 'TOC Management' );

			$this->data['nav_urls']['documentation_manuals'] = array(
				'href' => str_replace( '$1', PONYDOCS_DOCUMENTATION_PREFIX . 'Manuals', $wgArticlePath ),
				'text' => 'Manuals' );

			$this->data['nav_urls']['document_links'] = array(
				'href' => str_replace( '$1', 'Special:SpecialDocumentLinks?t=' . $wgTitle->getNsText() . ':' . htmlspecialchars($wgTitle->getPartialURL()), $wgArticlePath),
				'text' => 'What Links Here?');

		}
	}

	/**
	 * Output select options respecting a single-level parent/child product hierarchy
	 * 
	 * TODO: Handle multiple levels of parent/child relationships
	 * 
	 * @param string $parent  Short name of parent whose children we want to output
	 */
	private function hierarchicalProductSelect($parent = NULL) {
		foreach ($this->data['products'] as $data) {
			// We're at the top-level, output all top-level Products
			if ($parent === NULL && $data['parent'] == '') {
				$selected = !strcmp($data['name'], $this->data['selectedProduct']) ? 'selected="selected"' : '';
				echo '<option value="' . $data['name'] . '" ' . $selected . '>';
				echo $data['label'];
				echo "</option>\n";
				echo $this->hierarchicalProductSelect($data['name']);
			} else if ($parent !== NULL && $data['parent'] == $parent) {
				$selected = !strcmp($data['name'], $this->data['selectedProduct']) ? 'selected="selected"' : '';
				echo '<option class="child" value="' . $data['name'] . '" ' . $selected . '>';
				echo '-- ' . $data['label'];
				echo "</option>\n";
			}
		}
	}

	//  TODO:  SHOULD BE PRIVATE
	function contentActions( )
	{
		global $wgUser, $wgTitle, $wgArticle, $wgArticlePath, $wgScriptPath, $wgUser;

		$groups = $wgUser->getGroups( );
		$authProductGroup = PonyDocsExtension::getDerivedGroup();

		if( preg_match( '/' . PONYDOCS_DOCUMENTATION_PREFIX . '(.*):(.*):(.*):(.*)/i', $wgTitle->__toString( ), $match ))
		{
			if( in_array( PONYDOCS_EMPLOYEE_GROUP, $groups ) || in_array( $authProductGroup, $groups ))
			{
				array_pop( $match );  array_shift( $match );
				$title = PONYDOCS_DOCUMENTATION_PREFIX . implode( ':', $match );

				$this->data['content_actions']['viewall'] = array(
					'class' => '',
					'text' => 'View All',
					'href' => $wgScriptPath . '/index.php?title=Special:TopicList&topic=' . $title 
				);
			}
			if( $wgUser->isAllowed( 'branchtopic' ))
			{
				$this->data['content_actions']['branch'] = array(
					'class' => '',
					'icon' => 'icon-code-fork',
					'text'  => 'Branch',
					'href'	=> $wgScriptPath . '/Special:BranchInherit?titleName=' . $wgTitle->__toString()
				);
			}
		}
		else if( preg_match( '/' . PONYDOCS_DOCUMENTATION_PREFIX . '(.*):(.*)TOC(.*)/i', $wgTitle->__toString( ), $match ))
		{
			if( $wgUser->isAllowed( 'branchmanual' ))
			{
				$this->data['content_actions']['branch'] = array(
					'class' => '',
					'icon' => 'icon-code-fork',
					'text'  => 'Branch',
					'href'	=> $wgScriptPath . '/Special:BranchInherit?toc=' . $wgTitle->__toString( )
				);
			}
		}
	}

	/**
	 * Returns HTML containing the current documentation Status
	 * 
	 * @return HTML
	 */
	function documentationStatus( $classes = 'alert alert-warning' ) {
		$output = '';

		if ($this->inDocumentation && isset($this->data['manualname']) && isset($this->data['selectedProduct']) && isset($this->data['selectedVersion'])) {
			$version = PonyDocsProductVersion::GetVersionByName( $this->data['selectedProduct'], $this->data['selectedVersion'] );

			$statuses = array('Released', 'Unreleased', 'Preview');

			if ($version->getStatusCode() == 1 || $version->getStatusCode() == 2) {
				$output = '<div class="' . $classes . '">NOTICE: This documentation has not been released yet. Its current status is <strong>' . $statuses[$version->getStatusCode()] . '</strong>!</div>';
			}
		}
		
		return $output;
	}


	private function createAdminMenu() {
		$menu = array(
			'items' => array(
				array('label' => 'Documentation Admin'),
				array('label' => 'Manage Products', 'url' => '/Documentation:Products:'.PONYDOCS_LANGUAGE_DEFAULT),
				array('label' => 'Branch and Inherit', 'url' => '/Special:BranchInherit'),
				array('label' => 'Documentation Export', 'url' => '/Special:DocExport'),
				array('label' => 'Products'),
			)
		);

		$products = PonyDocsProduct::getDefinedProducts();
		if (!empty($products)) {
			foreach (PonyDocsProduct::getDefinedProducts() as $product) {
				$subitems = array(
					array('label' => 'Product Admin'),
					array('label' => 'Manage Versions', 'url' => '/Documentation:'.$product->getShortName().':Versions'),
					array('label' => 'Manage Manuals', 'url' => '/Documentation:'.$product->getShortName().':Manuals:'.PONYDOCS_LANGUAGE_DEFAULT),
					array('label' => 'Product Versions'),
				);

				foreach (PonyDocsProductVersion::GetVersions($product->getShortName()) as $version) {
					$subsubitems = array();

					$manuals = PonyDocsProductManual::getDefinedManuals($product->getShortName());
					if (isset($manuals) && !empty($manuals)) {
						foreach (PonyDocsProductManual::getDefinedManuals($product->getShortName()) as $manual) {
							$subsubitems[] = array('label' => $manual->getLongName() . ' (Language: '.PONYDOCS_LANGUAGE_DEFAULT.')');
							$subsubitems[] = array('label' => 'Manage Table of Contents', 'url' => '/Documentation:'.$product->getShortName().':'.$manual->getShortName().'TOC'.$version->getVersionName().':'.PONYDOCS_LANGUAGE_DEFAULT);
						}
					}

					$subitems[] = array('label' => $version->getVersionName(), 'items' => $subsubitems, 'url' => '#');
				}


				$menu['items'][] = array('label' => $product->getLongName(), 'url' => '#', 'items' => $subitems);
			}
		}
		else {
			$menu['items'][] = array('label' => 'No Products Defined', 'url' => '#', 'class' => 'disabled');
		}

		$menu['items'][] = array('label' => 'Site Admin');
		$menu['items'][] = array('label' => 'Manage User Rights', 'url' => '/Special:UserRights');
		$menu['items'][] = array('label' => 'Special Pages', 'url' => '/Special:SpecialPages');


		return $menu;
	}


	/**
	 * Generates a multidimensional array for use in a Primary Site Navigation
	 * 
	 * The primary contents of this navigation is an array of the products with released versions
	 * 
	 * @return array
	 */
	private function createNavigationMenu() {
		$menu = array();

		$menu['items'][] = array('divider' => true);
		$menu['items'][] = array('label' => 'Home', 'class' => $_SERVER['REQUEST_URI'] == '/Documentation' ? 'active' : '', 'url' => '/Documentation');

		foreach (PonyDocsProduct::getDefinedProducts() as $product) {
			//$latest = PonyDocsProductVersion::GetLatestVersionForUser($product->getShortName());
			$latest = PonyDocsProductVersion::GetLatestReleasedVersion($product->getShortName());

			if (!isset($latest) || empty($latest))
				continue;

			$class = '';
			$href = str_replace("/", "\/", "/Documentation/".$product->getShortName());
			if (preg_match("/^{$href}(.*)/", $_SERVER['REQUEST_URI'])) {
				$class = 'active';
			}

			$subitems = array();
			$manuals = PonyDocsProductManual::GetDefinedManuals( $product->getShortName() );
			if (!empty($manuals)) {
				foreach ($manuals as $manual) {
					$toc = new PonyDocsTOC($manual, $latest, $product);
					$toc_versions = $toc->getVersions();
					if (empty($toc_versions))
						continue;
					//$subitems[] = array('label' => $manual->getLongName(), 'url' => '/Documentation/' . $product->getShortName() . '/' . $latest->getVersionName() . '/' . $manual->getShortName());
					$subitems[] = array('label' => $manual->getLongName(), 'url' => '/Documentation/' . $product->getShortName() . '/latest/' . $manual->getShortName());
				}
			}

			if (!empty($subitems)) {
				$menu['items'][] = array('label' => $product->getLongName(), 'class' => $class, 'url' => '/Documentation/' . $product->getShortName(), 'items' => $subitems);
			}
			else {
				$menu['items'][] = array('label' => $product->getLongName(), 'class' => $class, 'url' => '/Documentation/' . $product->getShortName());
			}
		}

		return $menu;
	} // end private function createNavigationMenu


	private function createBreadcrumbMenu() {
		global $wgLanguageNames, $wgISO639LanguageCodes;

		$breadcrumbs = array();

		$parts = explode(":", $this->globals->wgTitle->__toString());

		if (count($parts) > 1) {
			if (preg_match('/^([a-zA-Z]{2})$/', $parts[count($parts)-1], $match)) {
				$language = array_pop($parts);
			}
			else {
				$language = PONYDOCS_LANGUAGE_DEFAULT;
			}
		} else {
			$sparts = explode("/", $this->globals->wgTitle->__toString());

			if (preg_match("/^([a-zA-Z]{2})\//", $sparts[0], $match))
				$language = strtolower(array_shift($sparts));
			else
				$language = PONYDOCS_LANGUAGE_DEFAULT;
		}

		$languageName = $wgLanguageNames[$language];

		if ($language != PONYDOCS_LANGUAGE_DEFAULT) {
			$breadcrumbs[] = array('label' => $languageName);
			$pieces[] = strtoupper($language);
		}

		if (count($parts) > 4 && $parts[0] == 'Documentation') {
			$page_title = PonyDocsTopic::FindH1ForTitle( $this->globals->wgTitle->__toString() );

			$parts = array($parts[0], $parts[1], $parts[4], $parts[2], $parts[3]);

			for ($x=0; $x<count($parts); $x++) {
				if ($x == 2) {
					$pieces[] = $this->data['selectedVersion'];
				}
				else {
					$pieces[] = $parts[$x];
				}

				$href = implode("/", $pieces);

				$new_title = Title::newFromText( $href );

				if ($x == 1) {
					$breadcrumbProduct = PonyDocsProduct::GetProductByShortName($parts[$x], $language);
					$breadcrumbs[] = array('label' => $breadcrumbProduct->getLongName(), 'url' => $new_title->getFullUrl());
				}
				else if ($x == 3) {
					$breadcrumbManual = PonyDocsProductManual::GetManualByShortName($breadcrumbProduct->getShortName(), $parts[$x], $language);
					$breadcrumbs[] = array('label' => $breadcrumbManual->getLongName(), 'url' => $new_title->getFullUrl());
				}
				else if ($x == 2) {
					$breadcrumbs[] = array('label' => $this->data['selectedVersion'], 'url' => $new_title->getFullUrl());
				}
				else if ($x != count($parts)-1) {
					$breadcrumbs[] = array('label' => $parts[$x], 'url' => $new_title->getFullUrl());
				}
				else {
					$breadcrumbs[] = array('label' => $page_title);
				}
			}
		}
		else if (count($parts) == 3 && $parts[0] == 'Documentation') {
			for ($x=0; $x<count($parts); $x++) {
				$pieces[] = $parts[$x];

				$href = implode("/", $pieces);

				$new_title = Title::newFromText( $href );

				if ($x == 1) {
					$breadcrumbProduct = PonyDocsProduct::GetProductByShortName($parts[$x], $language);
					$breadcrumbs[] = array('label' => $breadcrumbProduct->getLongName(), 'url' => $new_title->getFullUrl());
				}
				else if ($x == 3) {
					$breadcrumbManual = PonyDocsProductManual::GetManualByShortName($breadcrumbProduct->getShortName(), $parts[$x], $language);
					$breadcrumbs[] = array('label' => $breadcrumbManual->getLongName(), 'url' => $new_title->getFullUrl());
				}
				else if ($x != count($parts)-1) {
					$breadcrumbs[] = array('label' => $parts[$x], 'url' => $new_title->getFullUrl());
				}
				else {
					$breadcrumbs[] = array('label' => $parts[$x], 'class' => 'active');
				}
			}
		}
		else if (count($parts) == 2 && $parts[0] == 'Documentation') {
			for ($x=0; $x<count($parts); $x++) {
				$pieces[] = $parts[$x];

				$href = implode("/", $pieces);

				$new_title = Title::newFromText( $href );

				if ($x != count($parts)-1) {
					$breadcrumbs[] = array('label' => $parts[$x], 'url' => $new_title->getFullUrl());
				}
				else {
					$breadcrumbs[] = array('label' => "Manage {$parts[$x]}", 'class' => 'active');
				}
			}
		}
		else if (count($parts) == 2 && $parts[0] == 'Special') {
			$breadcrumbs[] = array('label' => 'Special Pages', 'url' => '/Special:SpecialPages');
			$breadcrumbs[] = array('label' => $this->globals->wgTitle->getSubpageText(), 'url' => $this->globals->wgTitle->getFullUrl());
		}
		else if (count($parts) == 2) {
			$breadcrumbs[] = array('label' => 'Home', 'url' => '/');
			$breadcrumbs[] = array('label' => $this->globals->wgTitle->getSubpageText(), 'url' => $this->globals->wgTitle->getFullUrl());
		}
		else {
			$parts = explode("/", $this->globals->wgTitle->__toString());

			if (count($parts) > 1) {
				if (preg_match('/^([a-zA-Z]{2})$/', $parts[0], $match)) {
					$language = strtolower(array_shift($parts));
				}
				else {
					$language = PONYDOCS_LANGUAGE_DEFAULT;
				}
			}

			$languageName = $wgISO639LanguageCodes[$language];

			$includeLang = false;
			if ($language != PONYDOCS_LANGUAGE_DEFAULT) {
				$includeLang = true;
				$breadcrumbs[] = array('label' => $languageName);
				$pieces[] = $language;
			}

			if (count($parts) > 0 && $parts[0] == PONYDOCS_DOCUMENTATION_NAMESPACE_NAME ) {
				for ($x=0; $x<count($parts); $x++) {
					$pieces[] = $parts[$x];

					$href = implode("/", $pieces);

					$new_title = Title::newFromText( $href );

					$product = PonyDocsProduct::GetProductByShortName($parts[$x], $language);
					$product_title = '';
					if (isset($product)) {
						$product_title = $product->getLongName();
					}

					if ($x != count($parts)-1) {
						if (!empty($product_title)) {
							$breadcrumbs[] = array('label' => $product_title, 'class' => 'active');
						}
						else {
							$breadcrumbs[] = array('label' => $parts[$x], 'url' => $new_title->getFullUrl());
						}
					}
					else {
						if (!empty($product_title)) {
							$breadcrumbs[] = array('label' => $product_title, 'class' => 'active');
						}
						else {
							$breadcrumbs[] = array('label' => $parts[$x], 'class' => 'active');
						}
					}
				}
			}
			else {
				$breadcrumbs[] = array('label' => $this->globals->wgTitle->getFullText(), 'url' => $this->globals->wgTitle->getFullUrl(), 'class' => 'active');
			}
		}

		return $breadcrumbs;
	}


	public function isAdmin() {
		if (in_array('docteam', $this->globals->wgUser->getGroups()) || in_array('sysop', $this->globals->wgUser->getGroups())) {
			return true;
		}
		
		return false;
	} // end function isDocumentationAdmin


} // end class PonyDocsTemplate extends QuickTemplate


?>