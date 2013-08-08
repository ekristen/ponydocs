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
		global $wgUser, $wgRequest, $wgSitename, $wgUploadDirectory;

		$noTOC = false;

		$dbr = wfGetDB( DB_SLAVE );

		$this->setHeaders( );
		$wgOut->setPagetitle( 'Documentation Export' );

		if ($wgRequest->wasPosted() && $_POST['toc'] !== 0) {
			$titles = array();
			if ($_POST['toc'] == 'all') {
				$res = $dbr->query("SELECT * FROM page WHERE page_namespace = '".PONYDOCS_DOCUMENTATION_NAMESPACE_ID."'");
				while ($row = $res->fetchObject()) {
					$titles[] = PONYDOCS_DOCUMENTATION_NAMESPACE_NAME . ':' . $row->page_title;
				}
				
				$filename = urlencode( $wgSitename . '-all-products-all-languages-' . wfTimestampNow() ) ;
			}
			else {
				$titles[] = $_POST['toc'];

				list($namespace, $productName, $manualNameTOCVersion, $language) = explode(":", $_POST['toc']);
				list($manualName, $versionName) = explode("TOC", $manualNameTOCVersion);

				$pProduct = PonyDocsProduct::GetProductByShortName($productName, $language);
				$vVersion = PonyDocsProductVersion::GetVersionByName($productName, $versionName);
				$mManual  = PonyDocsProductManual::GetManualByShortName($productName, $manualName, $language);

				$ponyTOC = new PonyDocsTOC($mManual, $vVersion, $pProduct, $language);
				list($toc, $prev, $next, $start) = $ponyTOC->loadContent();
				foreach($toc as $tocItem) {
					if (isset($tocItem['title']))
						$titles[] = $tocItem['title'];
				}

				$filename = urlencode( $wgSitename . '-' . $productName . '-' . $versionName . '-' . $manualName . '-' . $language . '-' . wfTimestampNow() );
			}

			if ($_POST['method'] == 'xml') {
				$filename = $filename . '.xml';
				
				$page_text = implode("\n", $titles);
				$history = WikiExporter::FULL;
				$list_authors = true;

				$wgOut->disable();
				// Cancel output buffering and gzipping if set
				// This should provide safer streaming for pages with history
				wfResetOutputBuffers();
				header( "Content-type: application/xml; charset=utf-8" );
				// Provide a sane filename suggestion
				$wgRequest->response()->header( "Content-disposition: attachment;filename={$filename}" );

				$this->doExport( $page_text, $history, $list_authors );
				return;
			} else if ($_POST['method'] == 'zip') {
				$filename = $filename . '.zip';
				$tempdir = $wgUploadDirectory . '/' . md5($filename . wfTimestampNow());

				mkdir($tempdir);

				foreach ($titles as $title) {
					$article = new Article( Title::newFromText( $title ) );

					$title_filename = str_replace(':', '_', $title) . ".txt";
					$f = fopen("{$tempdir}/{$title_filename}", 'w');
					fwrite($f, $article->getContent());
					fclose($f);

					$files[] = "{$tempdir}/{$title_filename}";
				}

				$this->create_zip($files, "{$wgUploadDirectory}/{$filename}");

				$filesize = filesize("{$wgUploadDirectory}/{$filename}");

				// Cleanup
				foreach($files as $file) {
					unlink($file);
				}
				rmdir($tempdir);

				$wgOut->disable();
				wfResetOutputBuffers();
				header("Content-Type: application/octet-stream;");
				header("Content-Transfer-Encoding: binary;");
				header("Content-Disposition: attachment; filename={$filename};");
				header("Content-Length: {$filesize};");
				readfile("{$wgUploadDirectory}/{$filename}");
				return;
			}
		}
		else if ($wgRequest->wasPosted() && $_POST['toc'] === 0) {
			// error
			$noTOC = true;
		}


		$wgOut->addHTML( '<h2>Documentation Export</h2>' );
		
		$wgOut->addHTML( '<p>Below you can choose a Table of Contents for a Product, Version, Manual, it will export the entire contents of the Manual including all revisions into an XML file that can be imported back into any WikiDocs system.</p>');

		if ($noTOC === true) {
			$wgOut->addHTML( '<div class="alert alert-danger"><strong>ERROR: You must select a Table of Contents or All Documentation</div> ');
		}

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
						if (preg_match( '/^V:' . $product->getShortName() . ':(.*):(.*)/i', $subrow->cl_to, $vmatch) && in_array($vmatch[1], $allowed_versions))
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
		<h3>Export Options</h3>
		<ul>
			<li><strong>XML (Native, Download)</strong>: This will export all the titles that fall under the selection you make in properly formatted XML file that can be used by any MediaWiki Import function</li>
			<li><strong>ZIP (Topic, TOC per File)</strong>: This will export one topic and one TOC per file in a zip file, this is useful when you need to send files to a translator for another language, they can return them in the same format.</li>
		</ul>
		<h3>Perform Export</h3>
		<form action="index.php?title=Special:DocExport" method="post" class="form-horizontal">
			<div class="control-group">
				<label for="toc" class="control-label">What to Export:</label>
				<div class="controls">
					<select name="toc" id="selectTOC" class="input input-xxlarge">
						<option value="0">Select Something to Export ...</option>
						<option value="all">All Documentation</option>
						<optgroup label="By Table of Contents">
							{$toc_output}
						</optgroup>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label for="method" class="control-label">Export Method:</label>
				<div class="controls">
					<select name="method" id="method" class="input">
						<option value="xml">XML (Download)</option>
						<option value="zip">ZIP (Page per File)</option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<input type="submit" id="selectGo" class="btn btn-primary" value="Export Documentation" />
				</div>
			</div>
		</form>
EOL;
		$wgOut->addHTML( $select );
		$wgOut->addHTML( $html );
	}
	
	
	public function create_zip($files = array(), $destination = '', $overwrite = false) {
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($destination) && !$overwrite) { return false; }
		//vars
		$valid_files = array();
		//if files were passed in...
		if(is_array($files)) {
			//cycle through each file
			foreach($files as $file) {
				//make sure the file exists
				if(file_exists($file)) {
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if(count($valid_files)) {
			//create the archive
			$zip = new ZipArchive();
			if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				return false;
			}
			//add the files
			foreach($valid_files as $file) {
				$zip->addFile($file, basename($file));
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

			//close the zip -- done!
			$zip->close();

			//check to make sure the file exists
			return file_exists($destination);
		}
		else
		{
			return false;
		}
	}
	
} // end class SpecialImportExport