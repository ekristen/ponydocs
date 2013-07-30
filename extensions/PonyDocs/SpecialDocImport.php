<?php
if( !defined( 'MEDIAWIKI' ))
	die( "PonyDocs MediaWiki Extension" );

/**
 * Needed since we subclass it;  it doesn't seem to be loaded elsewhere.
 */
require_once( $IP . '/includes/SpecialPage.php' );
require_once( $IP . '/includes/specials/SpecialImport.php');

/**
 * Register our 'Special' page so it is listed and accessible.
 */
$wgSpecialPages['DocImport'] = 'SpecialDocImport';


class SpecialDocImport extends SpecialImport {

	/**
	 * Just call the base class constructor and pass the 'name' of the page as defined in $wgSpecialPages.
	 *
	 * @returns SpecialBranchInherit
	 */
	public function __construct( )
	{
		SpecialPage::__construct( "DocImport" );
	}
	
	/**
	 * Returns a human readable description of this special page.
	 *
	 * @returns string
	 */
	public function getDescription( )
	{
		return 'Documentation Import';
	}

	function execute( $par ) {
		global $wgRequest, $wgUser, $wgOut;
		
		$this->setHeaders();
		$this->outputHeader();
		
		if ( wfReadOnly() ) {
			global $wgOut;
			$wgOut->readOnlyPage();
			return;
		}
		
		if( !$wgUser->isAllowed( 'import' ) && !$wgUser->isAllowed( 'importupload' ) )
			return $wgOut->permissionRequired( 'import' );

		# TODO: allow Title::getUserPermissionsErrors() to take an array
		# FIXME: Title::checkSpecialsAndNSPermissions() has a very wierd expectation of what
		# getUserPermissionsErrors() might actually be used for, hence the 'ns-specialprotected'
		$errors = wfMergeErrorArrays(
			$this->getTitle()->getUserPermissionsErrors(
				'import', $wgUser, true,
				array( 'ns-specialprotected', 'badaccess-group0', 'badaccess-groups' )
			),
			$this->getTitle()->getUserPermissionsErrors(
				'importupload', $wgUser, true,
				array( 'ns-specialprotected', 'badaccess-group0', 'badaccess-groups' )
			)
		);

		if( $errors ){
			$wgOut->showPermissionsErrorPage( $errors );
			return;
		}

		if ( $wgRequest->wasPosted() && $wgRequest->getVal( 'action' ) == 'submit' && $this->validateImport()) {
			//$this->doImport();
			die('checked out');
		}
		$this->showForm();
	}

	private function validateImport() {
		global $wgOut, $wgRequest, $wgUser, $wgImportSources, $wgExportMaxLinkDepth;
		$isUpload = false;
		$this->namespace = $wgRequest->getIntOrNull( 'namespace' );
		$sourceName = $wgRequest->getVal( "source" );

		$this->logcomment = $wgRequest->getText( 'log-comment' );
		$this->pageLinkDepth = $wgExportMaxLinkDepth == 0 ? 0 : $wgRequest->getIntOrNull( 'pagelink-depth' );

		if ( !$wgUser->matchEditToken( $wgRequest->getVal( 'editToken' ) ) ) {
			$source = new WikiErrorMsg( 'import-token-mismatch' );
		} elseif ( $sourceName == 'upload' ) {
			$isUpload = true;
			if( $wgUser->isAllowed( 'importupload' ) ) {
				$source = ImportStreamSource::newFromUpload( "xmlimport" );
			} else {
				return $wgOut->permissionRequired( 'importupload' );
			}
		} elseif ( $sourceName == "interwiki" ) {
			if( !$wgUser->isAllowed( 'import' ) ){
				return $wgOut->permissionRequired( 'import' );
			}
			$this->interwiki = $wgRequest->getVal( 'interwiki' );
			if ( !in_array( $this->interwiki, $wgImportSources ) ) {
				$source = new WikiErrorMsg( "import-invalid-interwiki" );
			} else {
				$this->history = $wgRequest->getCheck( 'interwikiHistory' );
				$this->frompage = $wgRequest->getText( "frompage" );
				$this->includeTemplates = $wgRequest->getCheck( 'interwikiTemplates' );
				$source = ImportStreamSource::newFromInterwiki(
					$this->interwiki,
					$this->frompage,
					$this->history,
					$this->includeTemplates,
					$this->pageLinkDepth );
			}
		} else {
			$source = new WikiErrorMsg( "importunknownsource" );
		}
		
		print "<pre>"; print_r($source); print "</pre>"; die('hihi');
	}
	
}