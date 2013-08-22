<?php
/**
 * PonyDocs Theme, based off of monobook
 * Gives ability to support documentation namespace.
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * @todo document
 * @file
 * @ingroup Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @ingroup Skins
 */
class SkinWikiDocs extends PonyDocsSkinTemplate {
	var $skinname = 'WikiDocs';
	var $stylename = 'WikiDocs';
	var $template = 'WikiDocsTemplate';
	var $useHeadElement = true;

	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		$out->addStyle( 'http://fonts.googleapis.com/css?family=Montserrat');
		$out->addStyle( 'WikiDocs/bootstrap/css/bootstrap.min.css');
		$out->addStyle( 'WikiDocs/bootstrap/css/bootstrap-responsive.min.css');
		$out->addStyle( 'WikiDocs/font-awesome/css/font-awesome.min.css');
		$out->addStyle( 'WikiDocs/main.css');
		
		$out->addMeta('viewport', 'width=device-width, initial-scale=1.0');
	}

	function tocIndent() {
		return "\n<ul class=\"nav nav-list\">";
	}

	/**
	 * Finish one or more sublevels on the Table of Contents
	 */
	function tocUnindent($level) {
		return "</li>\n" . str_repeat( "</ul>\n</li>\n", $level>0 ? $level : 0 );
	}

	/**
	 * End a Table Of Contents line.
	 * tocUnindent() will be used instead if we're ending a line below
	 * the new level.
	 */
	function tocLineEnd() {
		return "</li>\n";
 	}
	

	function tocLine( $anchor, $tocline, $tocnumber, $level, $sectionIndex = false ) {
		$classes = "toclevel-$level";
		if ( $sectionIndex !== false )
			$classes .= " tocsection-$sectionIndex";
		return "\n<li class=\"$classes\"><a href=\"#" .
			$anchor . '"><span class="tocnumber">' . '</span> <span class="toctext">' .
			$tocline . '</span></a>';
	}

	function tocList($toc) {
		$title = wfMsgHtml('toc') ;

		$output =<<<EOL
			<div id="contents" class="pull-right well well-small">
				<ul class="nav nav-list">
					<li class="nav-header">{$title}</li>
					<li>{$toc}</li>
				</ul>
			</div>
EOL;

		return $output;
	}
	
}



/**
 * @todo document
 * @ingroup Skins
 */
class WikiDocsTemplate extends PonyDocsTemplate {

	public function htmlAdminMenu() {
		if ($this->globals->wgUser->isLoggedIn() && $this->isAdmin()) {
			$dropdown = $this->generateDropdownHTML($this->adminMenu);
			$output =<<<EOL
<div class="btn-group pull-left visible-desktop">
	<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="#">
		Admin <span class="caret"></span>
	</a>
	{$dropdown}
</div>
EOL;
		} else {
			$output = '';
		}

		return $output;
	}

	public function navigationMenu() {
		$dropdown = $this->generateNavDropdownHTML($this->navigationMenu);

		$output =<<<EOL
<div class="navigation nav-collapse collapse">
	{$dropdown}
</div>
EOL;

		return $output;
	}

	public function generateBreadcrumbMenu() {
		$items = array();

		for ($x=0; $x<count($this->breadcrumbMenu); $x++) {
			$item = $this->breadcrumbMenu[$x];

			if ($x == count($this->breadcrumbMenu)-1) {
				$items[] = '<li class="active">'.$item['label'].'</li>';
			}
			else {
				if (isset($item['url'])) {
					$items[] = '<li><a href="'.$item['url'].'">'.$item['label'].'</a> <span class="divider">/</span></li>';
				}
				else {
					$items[] = '<li class="active">'.$item['label'].' <span class="divider">/</span></li>';
				}
			}
		}

		$item_output = implode("\n", $items);

		$output =<<<EOL
<div id="breadcrumbs" class="hidden-phone">
	<ul class="breadcrumb">
		{$item_output}
	</ul>
</div>
EOL;

		return $output;
	}


	public function searchForm() {
		$input = Html::input( 'search',
			isset( $this->data['search'] ) ? $this->data['search'] : '', 'search',
			array(
				'id' => 'searchInput',
				'class' => 'span2',
				'title' => $this->skin->titleAttrib( 'search' ),
				'accesskey' => $this->skin->accesskey( 'search' )
			) );
		global $wgScript;
		global $searchtitle;

		foreach ($wgNamespacesToBeSearchedDefault as $ns => $num) {
			$inputs .= '<input type="hidden" name="ns{$num}" value="1" />' . "\n";
		}

		$title = urlencode("Special:Search");

		$output =<<<EOL
		<div class="pull-right visible-desktop">
			<form action="{$wgScript}" id="searchform" class="navbar-form">
				<input type="hidden" name="title" value="{$title}"/>
				{$inputs}
				<div class="input-append">
					{$input}
					<input type="submit" name="fulltext" class="btn searchButton" id="mw-searchButton" value="Search" />
				</div>
			</form>
		</div>
EOL;

		return $output;
	}


	public function userMenu() {
		if (!$this->globals->wgUser->isLoggedIn()) {
			$output = '<a class="btn btn-primary pull-right" href="/Special:UserLogin"><i class="icon-signin"></i> '.wfMsgForContent('login').'</a>';
		}
		else {
			$first = array_shift($this->data['personal_urls']);

			foreach ($this->data['personal_urls'] as $key => $item) {
				if ($item['active'])
					$classes[] = 'active';
				if ($item['class'])
					$classes[] = $item['class'];


				$links[] = '<li id="' . Sanitizer::escapeId( "pt-$key" ) . '" class="' . implode(" ", $classes) . '">';
				$links[] = '<a href="'. htmlspecialchars($item['href']) .'" class="'. implode(" ", $classes) . '">'. htmlspecialchars($item['text']) .'</a>';
				$links[] = '</li>';
			};

			$link_output = implode("\n", $links);

			$output =<<<EOL
			<div class="btn-group pull-right visible-desktop">
				<a class="btn btn-primary" href="{$first['href']}">My Account</a>
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></button>
				<ul class="dropdown-menu">
					{$link_output}
				</ul>
			</div>
EOL;
		}

		return $output;
	}
	
	public function generateNavDropdownHTML($menu, $submenu = false, $level = 1) {
		$output = array();

		if ($level > 1) {
			$output[] = '<li class="dropdown '.$menu['class'].'">';
			$output[] = '<a class="dropdown-toggle" data-toggle="dropdown" href="'. $menu['url'] .'">' . $menu['label'] . ' <b class="caret"></b></a>';
			$output[] = '<ul class="dropdown-menu">';
		}
		else {
			$output[] = '<ul class="nav">';
		}

		foreach ($menu['items'] as $item) {
			if (isset($item['divider']) && $level == 1) {
				$output[] = ' <li class="divider-vertical"></li>';
			}
			else if (isset($item['url']) && isset($item['items'])) {
				$output[] = $this->generateNavDropdownHTML($item, true, $level + 1);
			}
			else if (isset($item['url'])) {
				// URL
				$output[] = ' <li class="'.$item['class'].'"><a href="'. $item['url'] . '">' . $item['label'] . '</a></li>';
			}
			else if (isset($item['divider'])) {
				$output[] = ' <li class="divider"></li>';
			}
			else {
				// Nav Header
				$output[] = ' <li class="nav-header">' . $item['label'] . '</li>';
			}
		}
		
		if ($submenu == false) {
			$output[] = '</ul>';
		}
		else {
			$output[] = '</ul>';
			$output[] = '</li>';
		}
		
		return implode("\n", $output);
	}

	public function generateDropdownHTML($menu = array(), $submenu = false, $level = 1) {
		$output = array();

		if ($submenu == false) {
			$output[] = '<ul class="dropdown-menu">';
		}
		else {
			$output[] = '<li class="dropdown-submenu">';
			$output[] = '<a href="#" class="'.$menu['class'].'">' . $menu['label'] . '</a>';
			$output[] = '<ul class="dropdown-menu">';
		}

		foreach ($menu['items'] as $item) {
			if (isset($item['url']) && isset($item['items'])) {
				$output[] = $this->generateDropdownHTML($item, true, $level + 1);
			}
			else if (isset($item['url'])) {
				// URL
				$output[] = ' <li><a href="'. $item['url'] . '" class="'.$item['class'].'">' . $item['label'] . '</a></li>';
			}
			else {
				// Nav Header
				$output[] = ' <li class="nav-header">' . $item['label'] . '</li>';
			}
		}

		if ($submenu == false) {
			$output[] = '</ul>';
		}
		else {
			$output[] = '</ul>';
			$output[] = '</li>';
		}
		
		return implode("\n", $output);
	}


	public function generateManualTocHtml() {
		if (!sizeof($this->data['manualtoc']))
			return '';

		foreach ( $this->data['manualtoc'] as $idx => $data ) {
			if ($data['level'] == 0) {
				$output[] = '<li class="nav-header">'.$data['text'].'</li>';
			}
			else if ($data['level'] == 1) {
				if ($data['current']) {
					$output[] = '<li class="active"><a href="'.$data['link'].'">'.$data['text'].'</a></li>';
				}
				else {
					$output[] = '<li><a href="'.$data['link'].'">'.$data['text'].'</a></li>';
				}
			}
			else {
				if ($data['current']) {
					$output[] = '<li class="active"><a href="'.$data['link'].'">'.$data['text'].'</a></li>';
				}
				else {
					$output[] = '<li><a href="'.$data['link'].'">'.$data['text'].'</a></li>';
				}
			}
		}

		$output[] = '<li class="divider"></li>';
		$output[] = '<li><a href="/index.php?title='.$this->globals->wgTitle->__toString().'&action=pdfbook"><i class="icon-book"></i> PDF '.wfMsgForContent('version').'</a></li>';

		$manual_output = implode("\n", $output);

		$output =<<<EOL
			<div class="well well-small">
				<ul class="nav nav-list">
					{$manual_output}
				</ul>
			</div>
EOL;

		return $output;
	}


	public function generateTopicPager() {
		$output[] = '<ul class="pager">';
		if (isset($this->data['tocprev']) && !empty($this->data['tocprev']['link'])) {
			$output[] = '<li class="previous"><a href="'.$this->data['tocprev']['link'].'">&larr; '.$this->data['tocprev']['text'].'</a></li>';
		}
		if (isset($this->data['tocnext']) && !empty($this->data['tocnext']['link'])) {
			$output[] = '<li class="next"><a href="'.$this->data['tocnext']['link'].'">'.$this->data['tocnext']['text'].' &rarr;</a></li>';
		}
		$output[] = '</ul>';

		return implode("\n", $output);
	}


	public function affectedVersionsHtml() {
		$ouput = '';

		if (isset($this->data['topicversions']) && !empty($this->data['topicversions'])) {
			foreach ($this->data['topicversions'] as $idx => $data) {
				$versions[] = '<a href="' . $data['href'] . '">' . $data['name'] . '</a>';
			}

			$output_versions = implode(' , ', $versions);

			$output =<<<EOL
				<div class="muted affectedVersions smallRoundedCorners <?php echo $this->data['versionclass']; ?>">
					<div class="pull-right">
						This documentation applies to the following versions of {$this->data['selectedProduct']}: {$output_versions}
					</div>
				</div>
EOL;
		}

		return $output;
	}
	
	public function offeredLanguagesHtml() {
		$output = '';

		if (isset($this->data['topictranslations']) && !empty($this->data['topictranslations'])) {
			foreach ($this->data['topictranslations'] as $idx => $data) {
				$languages[] = '<a href="/' . $data['href'] . '">' . $data['name'] . '</a>';
			}
		}

		$output_languages = implode(' , ', $languages);

		if (!empty($output_languages)) {
			$output =<<<EOL
				<div class="muted offeredLanguages">
					<div class="pull-right">
						Other Translations: {$output_languages} --&nbsp;
					</div>
				</div>
EOL;
		}

		return $output;
	}


	function productChangeHtml() {
		$items = array();
		foreach (PonyDocsProduct::GetDefinedProducts() as $product) {
			$selected = '';
			if ($product->getShortName() == $this->data['selectedProduct']) {
				$selected = ' selected="selected"';
			}
			$items[] = '<option value="'.$product->getShortName().'"'.$selected.'>'.$product->getLongName().'</option>';
		}

		$output_items = implode("\n", $items);

		$output =<<<EOL
			<div id="productChange" class="product" style="display: none;">';
				<select id="docsProductSelect" onchange="AjaxChangeProduct();" name="selectedProduct">
					{$output_items}
				</select>
			</div>
EOL;
		return $output;
	}

	function htmlProductChangeVersion() {
		$versions = PonyDocsProductVersion::GetVersionsForUser($this->data['selectedProduct']);
		
		if (empty($versions))
			return '';

		foreach ($versions as $version) {
			$latest = PonyDocsProductVersion::GetLatestVersion($this->data['selectedProduct'])->getVersionName();
			$extra = '';
			$selected = '';
			if ($this->isAdmin()) {
				$extra .= ' [' . $version->getVersionStatus() . ']';
			}
			if ($latest == $version->getVersionName()) {
				$extra .= ' ('.wfMsgForContent('histlast').')';
			}
			if ($this->data['selectedVersion'] == $version->getVersionName()) {
				$selected = ' selected="selected"';
			}
			
			$items[] = '<option value="'.$version->getVersionName().'"'.$selected.'>'.$version->getVersionName().$extra.'</option>';
		}

		$output_items = implode("\n", $items);

		$output =<<<EOL
			<div id="productVersionChange" class="productVersion pull-right">
				<input type="hidden" name="selectedProduct" value="{$this->data['selectedProduct']}" />
				<select id="docsVersionSelect" name="selectedVersion" onChange="AjaxChangeVersion();">
					{$output_items}
				</select>
			</div>
EOL;

		return $output;
	}


	function htmlContentActions() {
		if (!$this->globals->wgUser->isLoggedIn())
			return '';

		$items = array();
		foreach($this->data['content_actions'] as $key => $tab) {
			if ( in_array($key, array('talk')) ) continue;
			if ( $key == 'edit' )   $tab['icon'] = 'icon-edit';
			if ( $key == 'delete' ) $tab['icon'] = 'icon-trash';
			if ( $key == 'move' )   $tab['icon'] = 'icon-move';
			if ( $key == 'protect' )$tab['icon'] = 'icon-shield';
			if ( $key == 'watch' )  $tab['icon'] = 'icon-eye-open';
			if ( $key == 'viewall') $tab['icon'] = 'icon-list';
			if ( $key == 'history') $tab['icon'] = 'icon-undo';

			if( $tab['class'] ) { 
				if ($tab['class'] == 'selected') {
					$tab['class'] = 'active';
				}
				$class = $tab['class'];
			}
			else {
				$class = '';
			}

			$items[] = '<li class="'.$class.'">';
			$items[] = ' <a href="'.$tab['href'].'">';
			if (isset($tab['icon'])) {
				$items[] = '  <i class="'.$tab['icon'].'"></i> ';
			}
			$items[] = $tab['text'];
			$items[] = ' </a></li>';
		}

		$output_items = implode("\n", $items);

		$output =<<<EOL
			<ul class="nav nav-tabs">
				{$output_items}
			</ul>
EOL;

		return $output;
	}


	function htmlProductManuals() {
		$product = PonyDocsProduct::GetProductByShortName($this->data['selectedProduct'], $this->data['selectedLanguage']);
		$manuals = PonyDocsProductManual::GetDefinedManuals($this->data['selectedProduct'], $this->data['selectedLanguage']);
		$version = PonyDocsProductVersion::GetVersionByName($this->data['selectedProduct'], $this->data['selectedVersion']);
		$language = $this->data['selectedLanguage'];

		$nomv = true;
		foreach ($manuals as $manual) {
			$toc = new PonyDocsTOC($manual, $version, $product, $language);
			$toc_versions = $toc->getVersions();
			if (!empty($toc_versions))
				$nomv = false;
		}

		if (empty($manuals) || $nomv == true) {
			$output =<<<EOL
				<div class="alert alert-warning">
					* This product does not have any defined manuals for this version.<br>
					* If you have versions and manuals defined, then you need to create your Table of Contents (access via Admin Menu)
				</div>
EOL;
			return $output;
		}

		foreach ($manuals as $manual) {
			$toc = new PonyDocsTOC($manual, $version, $product);
			$toc_versions = $toc->getVersions();
			if (empty($toc_versions))
				continue;

			if ($this->data['selectedLanguage'] != PONYDOCS_LANGUAGE_DEFAULT) {
				$href = "/".strtoupper($this->data['selectedLanguage'])."/".PONYDOCS_DOCUMENTATION_NAMESPACE_NAME."/{$this->data['selectedProduct']}/{$version->getVersionName()}/{$manual->getShortName()}";
			}
			else {
				$href = "/".PONYDOCS_DOCUMENTATION_NAMESPACE_NAME."/{$this->data['selectedProduct']}/{$version->getVersionName()}/{$manual->getShortName()}";
			}

			$items[] =<<<EOL
				<li class="span6 pull-left">
					<div class="thumbnail">
						<div class="caption">
							<h3><a href="{$href}">{$manual->getLongName()}</a></h3>
							<p>{$manual->getDescription()}</p>
						</div>
					</div>
				</li>
EOL;
		}

		$output_items = implode("\n", $items);

		$output =<<<EOL
			<div class="productmanuallist">
				<ul class="thumbnails">
					{$output_items}
				</ul>
			</div>
EOL;

		return $output;
	}


	function htmlProducts() {
		$products = PonyDocsProduct::GetDefinedProducts($this->data['selectedLanguage']);

		foreach ($products as $product) {
			$version = PonyDocsProductVersion::LoadVersionsForProduct($product->getShortName());

			if (empty($version))
				continue;

			$hrefLang = '';
			if ($this->data['selectedLanguage'] != PONYDOCS_LANGUAGE_DEFAULT)
				$hrefLang = '/' . strtoupper($this->data['selectedLanguage']);

			$items[] =<<<EOL
				<li class="span6 pull-left">
					<div class="thumbnail">
						<div class="caption">
							<h3><a href="{$hrefLang}/Documentation/{$product->getShortName()}">{$product->getLongName()}</a></h3>
							<p>{$product->getDescription()}</p>
						</div>
					</div>
				</li>
EOL;
		}

		if (empty($items) && $this->isAdmin()) {
			$output =<<<EOL
			<div class="alert alert-warning">You do not have any products with versions defined. Once you define at least one product plus a version, you will see it populate here.</div>
EOL;
		} else {

			$output_items = implode("\n", $items);

			$output =<<<EOL
				<div class="productlist">
					<ul class="thumbnails">
						{$output_items}
					</ul>
				</div>
EOL;
		}

		return $output;
	} // end function htmlProducts


	function htmlDocumentationQuickToolbox() {
		foreach ( array('toc_mgmt', 'manuals_mgmt', 'versions_mgmt') as $url ) {
			if ($url == 'document_links') $icon = 'icon-link';
			if ($url == 'special_tocmgmt') $icon = 'icon-list-alt';

			if (isset($this->data['nav_urls'][$url])) {
				$docs_toolbox_items[] = '<li><a href="'. $this->data['nav_urls'][$url]['href'] . '"><i class="'.$icon.'"></i>'. $this->data['nav_urls'][$url]['text'] . '</a></li>';	
			}
		}

		$output_docs_toolbox = implode("\n", $docs_toolbox_items);

		$output =<<<EOL
			<div class="well well-small">
				<ul class="nav nav-list">
					<li class="nav-header">Docs Quick Toolbox</li>
					{$output_docs_toolbox}
				</ul>
			</div>
EOL;
		return $output;
	}


	function htmlDocumentationToolbox() {
		foreach ( array('special_tocmgmt', 'document_links') as $url ) {
			if ($url == 'document_links') $icon = 'icon-link';
			if ($url == 'special_tocmgmt') $icon = 'icon-list-alt';

			if (isset($this->data['nav_urls'][$url])) {
				$docs_toolbox_items[] = '<li><a href="'. $this->data['nav_urls'][$url]['href'] . '"><i class="'.$icon.'"></i>'. $this->data['nav_urls'][$url]['text'] . '</a></li>';	
			}
		}

		$output_docs_toolbox = implode("\n", $docs_toolbox_items);

		$output =<<<EOL
			<div class="well well-small">
				<ul class="nav nav-list">
					<li class="nav-header">Documentation Toolbox</li>
					{$output_docs_toolbox}
				</ul>
			</div>
EOL;
		return $output;
	}


	function htmlToolbox() {
		if ($this->data['notspecialpage']) { 
			if ($this->data['nav_urls']['recentchangeslinked'] ) {
				$toolbox_items[] = '<li><a href="'.$this->data['nav_urls']['recentchangeslinked']['href'].'"><i class="icon-archive"></i> '.$this->translator->translate('recentchangeslinked-toolbox').'</a></li>';
			}
		}
		if (isset( $this->data['nav_urls']['trackbacklink'] ) && $this->data['nav_urls']['trackbacklink'] ) {
			$toolbox_items[] = '<li id="t-trackbacklink"><a href="'.$this->data['nav_urls']['trackbacklink']['href'].'">'.$this->translator->translate('trackbacklink').'</a></li>';
		}

		foreach ( array('contributions', 'log', 'blockip', 'emailuser', 'upload', 'specialpages', 'whatlinkshere') as $special ) {
			if ($this->data['nav_urls'][$special]) {
				$icon = '';
				if ($special == 'upload') $icon = 'upload';
				if ($special == 'specialpages' ) $icon = 'cog';
				if ($special == 'whatlinkshere' ) $icon = 'link';

				$toolbox_items[] = '<li><a href="'.$this->data['nav_urls'][$special]['href'].'"><i class="icon-'.$icon.'"></i> '.$this->translator->translate($special).'</a></li>';
			}
		}

		if (!empty($this->data['nav_urls']['permalink']['href'])) {
			$toolbox_items[] = '<li><a href="'.$this->data['nav_urls']['permalink']['href'].'"><i class="icon-link"></i> '.$this->translator->translate('permalink').'</a></li>';
		} else if ($this->data['nav_urls']['permalink']['href'] === '') {
			$toolbox_items[] = '<li>'.$this->translator->translate('permalink').'</li>';
		}

		$output_toolbox = implode("\n", $toolbox_items);

		$toolbox_header = wfMsgForContent('toolbox');

		$output =<<<EOL
			<div class="well well-small">
				<ul class="nav nav-list">
					<li class="nav-header">{$toolbox_header}</li>
					{$output_toolbox}
				</ul>
			</div>
EOL;

		return $output;
	} // end function htmlToolbox


	function htmlFooterLinks() {
		$footerlinks = array('lastmod', 'viewcount', 'numberofwatchingusers', 'credits', 'copyright', 'privacy', 'about', 'disclaimer', 'tagline');
		$validFooterLinks = array();
		foreach ( $footerlinks as $aLink ) {
			if ( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
				$validFooterLinks[] = $aLink;
			}
		}

		if ( count( $validFooterLinks ) > 0 ) {
			ob_start();
			foreach ( $validFooterLinks as $aLink ) {
				if ( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
					$this->html($aLink);
					print " ";
				}
			}
			
			$links = ob_get_contents();
			ob_end_clean();
		}

		return $output=<<<EOL
			<p class="muted credit pull-right">
				{$links}
			</p>
EOL;
	} // end function htmlFooterLinks


	function phpErrors() {
		global $wgErrors;

		$items = array();

		foreach ($wgErrors as $error) {
			$items[$errno][] = '<li>'.$error['error'].' ['.$errno.']</li>';
		}

		$output = '';

		$output =<<<EOL
<div class="alert alert-danger">
	<ul>
</div>
EOL;

	}

}
