<?php

//
// Image resize function
//
function imageThumb($originalImage, $toWidth, $toHeight, $width, $height)
{
	global $type;

	$xscale = $width/$toWidth;
	$yscale = $height/$toHeight;

	// Recalculate new size with default ratio
	if ($yscale > $xscale)
	{
		$new_width = round($width * (1/$yscale));
		$new_height = round($height * (1/$yscale));
	}
	else
	{
		$new_width = round($width * (1/$xscale));
		$new_height = round($height * (1/$xscale));
	}

	$image_resized = imagecreatetruecolor($new_width, $new_height);

	if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG)
	{
		$trnprt_indx = imagecolortransparent($originalImage);

		// If we have a specific transparent color
		if ($trnprt_indx >= 0)
		{
			// Get the original image's transparent color's RGB values
			$trnprt_color = imagecolorsforindex($originalImage, $trnprt_indx);

			// Allocate the same color in the new image resource
			$trnprt_indx = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

			// Completely fill the background of the new image with allocated color
			imagefill($image_resized, 0, 0, $trnprt_indx);

			// Set the background color for new image to transparent
			imagecolortransparent($image_resized, $trnprt_indx);
		}
		// Always make a transparent background color for PNGs that don't have one allocated already
		elseif ($type == IMAGETYPE_PNG)
		{
			// Turn off transparency blending (temporarily)
			imagealphablending($image_resized, false);

			// Create a new transparent color for image
			$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);

			// Completely fill the background of the new image with allocated color.
			imagefill($image_resized, 0, 0, $color);

			// Restore transparency blending
			imagesavealpha($image_resized, true);
		}
	}

    imagecopyresampled($image_resized, $originalImage, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    return $image_resized;
}


		if (is_uploaded_file($uploaded_file['tmp_name']))
		{
			// Preliminary file check, adequate in most cases
			$allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
			if (!in_array($uploaded_file['type'], $allowed_types))
				message($lang_profile['Bad type']);

			// Move the file to the avatar directory. We do this before checking the width/height to circumvent open_basedir restrictions.
			if (!@move_uploaded_file($uploaded_file['tmp_name'], PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp'))
				message($lang_profile['Move failed'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.');

			list($width, $height, $type) = @getimagesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');

			// Determine type
			$extensions = null;
			if ($type == IMAGETYPE_GIF)
				$extensions = array('.gif', '.jpg', '.png');
			else if ($type == IMAGETYPE_JPEG)
				$extensions = array('.jpg', '.gif', '.png');
			else if ($type == IMAGETYPE_PNG)
				$extensions = array('.png', '.gif', '.jpg');

			// Now check the width/height
			if ( ( empty($width) || empty($height) || ( $width > $pun_config['o_avatars_width'] ) || ( $height > $pun_config['o_avatars_height'] ) ) && ( $pun_user['g_id'] > PUN_MOD && $pun_user['g_id'] != PUN_GLMOD ) )
			{
				// Attempt to resize if GD is installed with support for the uploaded image type, as well as JPG for the output
				$check_type = str_replace(array(1, 2, 3), array('IMG_GIF', 'IMG_JPG', 'IMG_PNG'), $type);
				if (extension_loaded('gd') && (imagetypes() && constant($check_type)) && (imagetypes() && IMAGETYPE_JPEG))
				{
					// Load the image for processing
					if ($type == IMAGETYPE_GIF)
						$src_img = @imagecreatefromgif(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');
					elseif ($type == IMAGETYPE_JPEG)
						$src_img = @imagecreatefromjpeg(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');
					elseif ($type == IMAGETYPE_PNG)
						$src_img = @imagecreatefrompng(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');

					if ($src_img)
					{
						$new_img = imageThumb($src_img, $pun_config['o_avatars_width'], $pun_config['o_avatars_height'], $width, $height);

						// Delete the old image and write the newly resized one
						@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');

						if ($type == IMAGETYPE_GIF)
							$src_img = imagegif($new_img, PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');
						elseif ($type == IMAGETYPE_JPEG)
							$src_img = imagejpeg($new_img, PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp', 85);
						elseif ($type == IMAGETYPE_PNG)
							$src_img = imagepng($new_img, PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');
					}
					// Something went wrong while attempting to load the image for processing
					else
					{
						@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');
						message('An unexpected error occured while attempting to resize the image.');
					}
				}
				// No GD installed or image type not supported - can't resize
				else
				{
					@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');
					message($lang_profile['Too wide or high'].' '.$pun_config['o_avatars_width'].'x'.$pun_config['o_avatars_height'].' '.$lang_profile['pixels'].'.');
				}
			}
			else if ($type == IMAGETYPE_GIF && $uploaded_file['type'] != 'image/gif')	// Prevent dodgy uploads
			{
				@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp');
				message($lang_profile['Bad type']);
			}

			// Make sure the file isn't too big
			if ( (filesize(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp') > $pun_config['o_avatars_size']) && ( $pun_user['g_id'] > PUN_MOD && $pun_user['g_id'] != PUN_GLMOD ) )
				message($lang_profile['Too large'].' '.$pun_config['o_avatars_size'].' '.$lang_profile['bytes'].'.');

			// Delete any old avatars and put the new one in place
			@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.$extensions[0]);
			@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.$extensions[1]);
			@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.$extensions[2]);
			@rename(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.'.tmp', PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.$extensions[0]);
			@chmod(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$id.$extensions[0], 0644);
		}
		else
			message($lang_profile['Unknown failure']);

		redirect('profile.php?section=personality&amp;id='.$id, $lang_profile['Avatar upload redirect']);