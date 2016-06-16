<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Mumble Viewer Demo</title>
	<!-- Copy everything below this line -->
	<link rel="stylesheet" href="mumbleChannelViewer.css" type="text/css" />
	<!-- Copy everything above this line -->
</head>
<body>
	<p>This is a demo of the Mumble Viewer. Make sure to set the correct value for <em>$dataUrl</em> first. Be sure to also include the CSS link to <em>mumbleChannelViewer.css</em> in your page.</p>

	<!-- Copy everything below this line -->
	<div id="mumbleViewer">
		<?php
			require_once( 'mumbleChannelViewer.php' );
			$dataUrl = 'http://json/serverId=1';		// Enter your JSON URL between the single quotes (')
			echo MumbleChannelViewer::render( $dataUrl, 'json' );
		?>
	</div>
	<!-- Copy everything above this line -->
</body>
</html>