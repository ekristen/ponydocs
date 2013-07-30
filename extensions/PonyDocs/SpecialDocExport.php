<?php
if( !defined( 'MEDIAWIKI' ))
	die( "PonyDocs MediaWiki Extension" );

/**
 * Needed since we subclass it;  it doesn't seem to be loaded elsewhere.
 */
require_once( $IP . '/includes/SpecialPage.php' );
require_once( $IP . '/includes/specials/SpecialExport.php');

/**
 * Register our 'Special' page so it is listed and accessible.
 */
$wgSpecialPages['DocExport'] = 'SpecialDocExport';

class SpecialDocExport extends SpecialExport
{
	/**
	 * Just call the base class constructor and pass the 'name' of the page as defined in $wgSpecialPages.
	 *
	 * @returns SpecialBranchInherit
	 */
	public function __construct( )
	{
		SpecialPage::__construct( "DocExport" );
	}
	
	/**
	 * Returns a human readable description of this special page.
	 *
	 * @returns string
	 */
	public function getDescription( )
	{
		return 'Documentation Export';
	}


	/**
	 * This is called upon loading the special page.  It should write output to the page with $wgOut.
	 */
	public function execute( $par )
	{
		global $wgOut, $wgArticlePath, $wgScriptPath;
		global $wgUser, $wgRequest, $wgSitename;

		$dbr = wfGetDB( DB_SLAVE );

		$this->setHeaders( );
		$wgOut->setPagetitle( 'Documentation Export' );

		if ($wgRequest->wasPosted()) {
			$titles = array();
			$titles[] = $_POST['toc'];

			list($namespace, $productName, $manualNameTOCVersion) = explode(":", $_POST['toc']);
			list($manualName, $versionName) = explode("TOC", $manualNameTOCVersion);

			$pProduct = PonyDocsProduct::GetProductByShortName($productName);
			$vVersion = PonyDocsProductVersion::GetVersionByName($productName, $versionName);
			$mManual  = PonyDocsProductManual::GetManualByShortName($productName, $manualName);

			$ponyTOC = new PonyDocsTOC($mManual, $vVersion, $pProduct);
			list($toc, $prev, $next, $start) = $ponyTOC->loadContent();
			foreach($toc as $tocItem) {
				if (isset($tocItem['title']))
					$titles[] = $tocItem['title'];
			}

			$page_text = implode("\n", $titles);
			$history = WikiExporter::FULL;
			$list_authors = true;

			$wgOut->disable();
			// Cancel output buffering and gzipping if set
			// This should provide safer streaming for pages with history
			wfResetOutputBuffers();
			header( "Content-type: application/xml; charset=utf-8" );
			// Provide a sane filename suggestion
			$filename = urlencode( $wgSitename . '-' . $productName . '-' . $versionName . '-' . $manualName . '-' . wfTimestampNow() . '.xml' );
			$wgRequest->response()->header( "Content-disposition: attachment;filename={$filename}" );

			$this->doExport( $page_text, $history, $list_authors );
			return;
		}



		$wgOut->addHTML( '<h2>Documentation Export</h2>' );
		
		$wgOut->addHTML( '<p>Below you can choose a Table of Contents for a Product, Version, Manual, it will export the entire contents of the Manual including all revisions into an XML file that can be imported back into any WikiDocs system.</p>');

		$tocs = array();

		$products = PonyDocsProduct::GetDefinedProducts();
		foreach ($products as $product) {
			$manuals = PonyDocsProductManual::GetDefinedManuals( $product->getShortName() );

			foreach (PonyDocsProductVersion::GetVersions($product->getShortName()) as $v)
				$allowed_versions[] = $v->getVersionName();

			foreach ($manuals as $manual) {

				$qry = "SELECT DISTINCT(cl_sortkey) 
						FROM categorylinks 
						WHERE LOWER(cast(cl_sortkey AS CHAR)) LIKE 'documentation:" . $dbr->strencode( strtolower( $product->getShortName() ) ) . ':' . $dbr->strencode( strtolower( $manual->getShortName( ))) . "toc%'";

				$res = $dbr->query( $qry );

				while( $row = $dbr->fetchObject( $res ))
				{
					$subres = $dbr->select( 'categorylinks', 'cl_to', "cl_sortkey = '" . $dbr->strencode( $row->cl_sortkey ) . "'", __METHOD__ );
					$versions = array( );

					while( $subrow = $dbr->fetchObject( $subres ))
					{
						if (preg_match( '/^V:' . $product->getShortName() . ':(.*)/i', $subrow->cl_to, $vmatch) && in_array($vmatch[1], $allowed_versions))
							$versions[] = $vmatch[1];
					}

					if (sizeof($versions))
						$tocs[$row->cl_sortkey] = $row->cl_sortkey . ' - Versions: ' . implode( ' | ', $versions );
				}
			}
		}

		foreach ($tocs as $key => $toc) {
			$toc_items[] = '<option value="'.$key.'">'.$toc.'</option>';
		}

		$toc_output = implode("\n", $toc_items);

		$select =<<<EOL
		<form action="index.php?title=Special:DocExport" method="post">
		<label for="toc">
			Table of Contents: 
			<select name="toc" id="selectTOC" class="input input-xxlarge">
				{$toc_output}
			</select>
		</label>
			<input type="submit" id="selectGo" class="btn btn-primary" value="Export Manual" />
		</form>
EOL;
		$wgOut->addHTML( $select );
		$wgOut->addHTML( $html );
	}
	
	
} // end class SpecialImportExport