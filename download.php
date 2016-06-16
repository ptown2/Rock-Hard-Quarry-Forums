<?php
	define('PUN_ROOT', dirname(__FILE__).'/');

	require PUN_ROOT.'include/common.php';

	if(!function_exists('mime_content_type')) {
		function mime_content_type($filename) {
			$mime_types = array(
				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',

				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',

				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',

				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',

				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',

				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',

				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);

			$exttbl = explode('.',$filename);
			$exttbl2 = array_pop($exttbl);
			$ext = strtolower($exttbl2);

			if (array_key_exists($ext, $mime_types)) {
				return $mime_types[$ext];
			} elseif (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);

				return $mimetype;
			} else {
				return 'application/octet-stream';
			}
		}
	}

	// Variables
	$file = isset($_GET['file']) ? pun_trim(basename($_GET['file'])) : null;
	$referral = isset($_SERVER['HTTP_REFERER']) ? pun_trim($_SERVER['HTTP_REFERER']) : 'index.php';

	// Post Variables
	$file_post = isset($_POST['file']) ? pun_trim($_POST['file']) : null;
	$referral_post = isset($_POST['referral']) ? pun_trim($_POST['referral']) : null;
	$file_dir = PUN_ROOT.'downloads/'.$filepost.''.$file;

	// Check if the download file exists.
	if ((!isset($file) || !file_exists($file_dir)) && !isset($file_post))
		redirect($referral, 'Invalid Download File!');

	$file_type = pathinfo($file_dir, PATHINFO_EXTENSION);
	$file_mime_type = mime_content_type($file_dir);

	$file_size_raw = filesize($file_dir);
	$file_size = file_size($file_size_raw);

	// Check if the download request is a post one.
	if ($file_post && file_exists($file_dir)) {
		confirm_referrer('download.php');

		header('Content-disposition: attachment; filename='.$file_post);
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$file_size_raw);
		header('Content-Type: '.$file_mime_type);
		header('Content-Description: File Transfer');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		ob_clean();
		flush();

		readfile($file_dir);

		redirect(isset($referral_post) ? $refferal_post : 'index.php', 'Header failed to proccess, redirecting back to original site.');
	}

	// Define FluxBB Stuff
	define('PUN_ACTIVE_PAGE', 'index');
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Download');

	require PUN_ROOT.'header.php';
?>
<div id="rules" class="blockform">
	<div class="hd"><h2><span>Download File - <?php echo $file; ?></span></h2></div>

	<div class="box" style="padding: 12px 25% 12px 25%;">
		<span style="float: left; margin-top: 12px; margin-right: 12px;"><img width="256px" height="256px" src="img/fileicons/<?php echo $file_type; ?>.png"></span>

		<form id="download" action="download.php" method="post" style="height: 256px;">
			<div style="font-size: 18px; vertical-align: middle;">
				File Name: <?php echo $file; ?><br /><br />
				File Size: <?php echo $file_size; ?><br /><br />
				File Type: <?php echo $file_mime_type; ?><br />
				<br />
				<a style="font-size: 32px;" href="javascript:;" onclick="document.getElementById('download').submit();">DOWNLOAD NOW!</a>
			</div>

			<input type="hidden" name="file" value="<?php echo $file; ?>"/>
			<input type="hidden" name="referral" value="<?php echo $referral; ?>"/>
		</form>
	</div>
</div>
<?php require PUN_ROOT.'footer.php'; ?>