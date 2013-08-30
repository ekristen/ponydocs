<!DOCTYPE html>
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
<head>
	<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php $this->html('headlinks') ?>
	<title><?php $this->text('pagetitle') ?></title>
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Montserrat" />
	<link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/bootstrap/css/bootstrap.min.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
	<link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/bootstrap/css/bootstrap-responsive.min.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
	<link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/font-awesome/css/font-awesome.min.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
	<link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
	<?php print Skin::makeGlobalVariablesScript($this->data); ?>
	<script type="text/javascript" src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/jquery.js"></script>
	<?php $this->html('headscripts'); ?>
	<?php print $wgOut->getScript(); ?>
	<script type="text/javascript">
		function ponyDocsOnLoad() {}

		function AjaxChangeProduct_callback( o ) {
			document.getElementById('docsProductSelect').disabled = true;
			var s = new String( o.responseText );
			document.getElementById('docsProductSelect').disabled = false;
			window.location.href = s;
		}

		function AjaxChangeProduct( ) {
			var productIndex = document.getElementById('docsProductSelect').selectedIndex;
			var product = document.getElementById('docsProductSelect')[productIndex].value;
			var title = '<?php $this->jstext('thispage'); ?>';
			sajax_do_call( 'efPonyDocsAjaxChangeProduct', [product,title], AjaxChangeProduct_callback );
		}

		function AjaxChangeVersion_callback( o ) {
			document.getElementById('docsVersionSelect').disabled = true;
			var s = new String( o.responseText );
			document.getElementById('docsVersionSelect').disabled = false;
			window.location.href = s;
		}

		function AjaxChangeVersion( ) {
			var productIndex = document.getElementById('docsProductSelect').selectedIndex;
			var product = document.getElementById('docsProductSelect')[productIndex].value;
			var versionIndex = document.getElementById('docsVersionSelect').selectedIndex;
			var version = document.getElementById('docsVersionSelect')[versionIndex].value;
			var title = '<?php $this->jstext('thispage'); ?>';
			sajax_do_call( 'efPonyDocsAjaxChangeVersion', [product,version,title], AjaxChangeVersion_callback );
		}

		function changeManual(){
			var url = $("#docsManualSelect").val();
			if (url != ""){
				window.location.href = url;
			}
		}
	</script>
</head>
<body <?php if($this->data['body_ondblclick']) { ?>ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
	<?php if($this->data['body_onload']) { ?>onload="<?php $this->text('body_onload') ?>"<?php } ?>
	class="mediawiki <?php $this->text('nsclass') ?> <?php $this->text('dir') ?> <?php $this->text('pageclass') ?>">

<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="brand" href="/"><?php echo $this->globals->wgSitename; ?></a>

			<?php print $this->htmlAdminMenu(); ?>

			<?php print $this->navigationMenu(); ?>

			<?php print $this->userMenu(); ?>

			<?php print $this->searchForm(); ?>
		</div>
	</div>
</div>


<?php if ($this->inDocumentation && isset($this->data['titletext'])): ?>
<div id="pageHeader" class="container">
	<div class="row">
		<div class="span12">
			<h1 class="page-header">
				<?php print $this->data['headertext']; ?>
				<?php print $this->productChangeHtml(); ?>
				<?php print $this->htmlProductChangeVersion(); ?>
			</h1>
		</div>
	</div>
</div> <!-- end div#documentationHeader -->
<?php endif; ?>


<div class="container"> 
	<?php print $this->generateBreadcrumbMenu(); ?>

	<?php print $this->documentationStatus(); ?>

	<?php print $this->phpErrors(); ?>

	<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>

	<div class="row">
		<div class="span12 pull-right">
			<div class="tabbable">
				<?php print $this->htmlContentActions(); ?>

				<div class="tab-content">

					<div class="content" id="content">
						<?php $this->html('bodytext') ?>

						<?php print $this->generateTopicPager(); ?>

						<?php print $this->affectedVersionsHtml(); ?>
					</div>
				</div>
			</div> <!-- end div.tabbable.tabs-right -->
		</div> <!-- end div.span9.pull-right -->

	</div> <!-- end div.row -->
</div> <!-- end div.container -->


<div id="footer">
	<div class="container">
		<?php print $this->htmlFooterLinks(); ?>
	</div>
</div> <!-- end div#footer -->

<script type="text/javascript" src="/skins/WikiDocs/bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="/skins/WikiDocs/sauce.js"></script>

<?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
<?php $this->html('reporttime') ?>

<?php 
if ($this->data['debug']) {
	$this->text( 'debug' );
}
?>

</body>
</html>
