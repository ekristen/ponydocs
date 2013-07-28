<?php
if( !defined( 'MEDIAWIKI' ))
	die( "PonyDocs MediaWiki Extension" );

require_once( "$IP/includes/Article.php" );

/**
 * Provides abstract class for PonyDocs Articles
 */

abstract class PonyDocsArticle extends Article
{

	public $metadata = array();

	public function setMetadata($metadata) {
		$this->metadata = array_merge($this->metadata, $metadata);
	}

}

?>