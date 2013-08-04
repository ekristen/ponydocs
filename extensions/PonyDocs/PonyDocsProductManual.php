<?php
if( !defined( 'MEDIAWIKI' ))
	die( "PonyDocs MediaWiki Extension" );


/**
 * An instance represents a single PonyDocs manual based upon the short/long name.  It also contains static
 * methods and data for loading the global list of manuals from the special page. 
 */
class PonyDocsProductManual
{
	/**
	 * Short/abbreviated name for the manual used in page paths;  it is always lowercase and should be
	 * alphabetic but is not required.
	 *
	 * @var string
	 */
	protected $mShortName;

	/**
	 * Long name for the manual which functions as the 'display' name in the list of manuals and so
	 * forth.
	 *
	 * @var string
	 */
	protected $mLongName;

	protected $pName;

	/**
	 * Our list of manuals loaded from the special page, stored statically.  This only contains the manuals
	 * which have a TOC defined and tagged to the currently selected version.
	 *
	 * @var array
	 */
	static protected $sManualList = array( );

	/**
	 * Our COMPLETE list of manuals.
	 *
	 * @var array
	 */
	static protected $sDefinedManualList = array( );

	/**
	 * Constructor is simply passed the short and long (display) name.  We convert the short name to lowercase
	 * immediately so we don't have to deal with case sensitivity.
	 *
	 * @param string $shortName Short name used to refernce manual in URLs.
	 * @param string $longName Display name for manual.
	 */
	public function __construct( $pName, $shortName, $longName = '', $description = '' )
	{
		//$this->mShortName = strtolower( $shortName );
		$this->mShortName = preg_replace( '/([^' . PONYDOCS_PRODUCTMANUAL_LEGALCHARS . '])/', '', $shortName );
		$this->pName = $pName;
		$this->mLongName = strlen( $longName ) ? $longName : $shortName;
		$this->mDescription = strlen( $description ) ? $description : '';
	}

	public function getShortName( )
	{
		return $this->mShortName;
	}
	
	public function getLongName( )
	{
		return $this->mLongName;
	}

	public function getProductName( )
	{
		return $this->pName;
	}
	
	public function getDescription()
	{
		return $this->mDescription;
	}

	/**
	 * This loads the list of manuals BASED ON whether each manual defined has a TOC defined for the
	 * currently selected version or not.
	 *
	 * @param boolean $reload
	 * @return array
	 */

	static public function LoadManualsForProduct( $productName, $reload = false )
	{
		$dbr = wfGetDB( DB_SLAVE );

		/**
		 * If we have content in our list, just return that unless $reload is true.
		 */
		if( isset(self::$sManualList[$productName]) && sizeof( self::$sManualList[$productName] ) && !$reload )
			return self::$sManualList[$productName];

		self::$sManualList[$productName] = array( );

		// Use 0 as the last parameter to enforce getting latest revision of 
		// this article.
		$article = new Article( Title::newFromText( PONYDOCS_DOCUMENTATION_PREFIX . $productName . PONYDOCS_PRODUCTMANUAL_SUFFIX ), 0);
		$content = $article->getContent( );

		if( !$article->exists( ))
		{
			/**
			 * There is no manuals file found -- just return.
			 */
			return array( );
		}

		/**
		 * The content of this topic should be of this form:
		 * {{#manual:shortName|longName}}
		 * ...
		 * 
		 * There is a user defined parser hook which converts this into useful output when viewing as well.
		 * 
		 * Then query categorylinks to only add the manual if it is for the selected product and has a tagged TOC file with the selected version.
		 * Otherwise, skip it!
		 */

		if( !preg_match_all( '/{{#manual:\s*(.*)[|](.*)[|](.*)\s*}}/i', $content, $matches, PREG_SET_ORDER ))
			return array( );

		foreach( $matches as $m )
		{
			$pManual = new PonyDocsProductManual( $productName, $m[1], $m[2], $m[3] );
			self::$sDefinedManualList[$productName][strtolower($pManual->getShortName( ))] = $pManual;

			$res = PonyDocsCategoryLinks::getTOCByProductManualVersion($productName, $pManual->getShortName(), PonyDocsProductVersion::GetSelectedVersion($productName));

			if( !$res->numRows( )) {
				continue;
			}

			self::$sManualList[$productName][strtolower($m[1])] = $pManual;
		}

		return self::$sManualList[$productName];
	}

	/**
	 * Just an alias.
	 *
	 * @static
	 * @return array
	 */
	static public function GetManuals( $productName )
	{
		return self::LoadManualsForProduct( $productName );
	}

	/**
	 * Return list of ALL defined manuals regardless of selected version.
	 *
	 * @static
	 * @returns array
	 */
	static public function GetDefinedManuals( $productName )
	{
		self::LoadManualsForProduct( $productName );
		return self::$sDefinedManualList[$productName];
	}

	/**
	 * Our manual list is a map of 'short' name to the PonyDocsManual object.  Returns it, or null if not found.
	 *
	 * @static
	 * @param string $shortName
	 * @return PonyDocsManual&
	 */
	static public function & GetManualByShortName( $productName, $shortName )
	{
		$convertedName = preg_replace( '/([^' . PONYDOCS_PRODUCTMANUAL_LEGALCHARS . ']+)/', '', $shortName );
		if( self::IsManual( $productName, $convertedName ))
			return self::$sDefinedManualList[$productName][strtolower($convertedName)];
		return null;
	}

	/**
	 * Test whether a given manual exists (is in our list).  
	 *
	 * @static
	 * @param string $shortName
	 * @return boolean
	 */
	static public function IsManual( $productName, $shortName )
	{
		// We no longer specify to reload the manual data, because that's just 
		// insanity.
		self::LoadManualsForProduct($productName, false);
		// Should just force our manuals to load, just in case.
		$convertedName = preg_replace( '/([^' . PONYDOCS_PRODUCTMANUAL_LEGALCHARS . ']+)/', '', $shortName );
		return isset( self::$sDefinedManualList[$productName][strtolower($convertedName)] );
	}

	/**
	 * Return the current manual object based on the title object;  returns null otherwise.
	 *
	 * @static
	 * @return PonyDocsManual
	 */
	static public function GetCurrentManual($productName, $title = null )
	{
		global $wgTitle;
		$targetTitle = $title == null ? $wgTitle : $title;
		$pcs = explode( ':', $targetTitle->__toString( ));
		if( !isset($pcs[2]) || !self::IsManual( $productName, $pcs[2] ))
			return null;
		return self::GetManualByShortName( $productName, $pcs[2] );
	}

	function getTranslations() {
		
	}
};

/**
 * End of file.
 */
?>