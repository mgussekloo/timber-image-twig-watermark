<?php

namespace Gusmanson;

/*
 * Watermark an image with a (preferably transparent) watermark-image.
 *
 */
class WatermarkTwigfilterHelper {

	/**
	 *
	 *
	 *
	 */

	public static function watermark( $filename_or_object=null, $watermark_image=null, $force=false ) {
		// make sure the image exists, prepare the output, etc.

		if (is_null( $filename_or_object )) {
			return $filename_or_object;
		}

		if (is_null( $watermark_image )) {
			return $filename_or_object;
		}

		if (!file_exists( $watermark_image )) {
			$watermark_image = get_theme_file_path( $watermark_image );
		}

		$load_filename = null;
		if ( is_object( $filename_or_object ) && method_exists( $filename_or_object, 'src') ) {
			$load_filename = $filename_or_object->src();
		}

		if ( is_string( $filename_or_object ) ) {
			$load_filename = $filename_or_object;
		}

		if (is_null( $load_filename ) ) {
			return $filename_or_object;
		}

		$subfolder = null;
		if ( \Timber\URLHelper::is_url($load_filename) ) {
			$load_filename = \Timber\URLHelper::url_to_file_system($load_filename);
			$subfolder = dirname( $load_filename );
		}

		if (is_null($subfolder)) {
			return $filename_or_object;

		}

		if (pathinfo($load_filename, PATHINFO_EXTENSION) == '') {
			return $filename_or_object;
		}

		$save_filename = $subfolder . '/' . sprintf('%s-watermark-%s.%s',
			pathinfo($load_filename, PATHINFO_FILENAME),
			substr(md5($load_filename), 0, 7),
			pathinfo($load_filename, PATHINFO_EXTENSION)
		);

		if ( $force == false && file_exists( $save_filename) ) {
			return \Timber\URLHelper::file_system_to_url($save_filename);
		}

		// Load the stamp and the photo to apply the watermark to
		$source = self::getImage($load_filename);
		$watermark = self::getImage($watermark_image);

		$wp_image = wp_get_image_editor( $load_filename );
		$quality = $wp_image->get_quality();

		// Set the margins for the stamp and get the height/width of the stamp image
		$margin_right = 10;
		$margin_bottom = 10;

		$sx = imagesx($source);
		$sy = imagesy($source);

		$wx = imagesx($watermark);
		$wy = imagesy($watermark);


		// too big?
		if ($wy + $margin_bottom > $sy || $wx + $margin_right > $sx) {
			return $filename_or_object;
		}

		// Copy the stamp image onto our photo using the margin offsets and the photo
		// width to calculate positioning of the stamp.
		imagecopy(
			$source,
			$watermark,
			$sx - $wx - $margin_right,
			$sy - $wy - $margin_bottom,
			0,
			0,
			$wx,
			$wy
		);

		$result = self::saveImage($source, $save_filename, $quality);
		return \Timber\URLHelper::file_system_to_url($save_filename);
	}

	static function getImage($src) {
		$func = 'imagecreatefromjpeg';
		$ext = pathinfo($src, PATHINFO_EXTENSION);
		if ( $ext == 'gif' ) {
			$func = 'imagecreatefromgif';
		} else if ( $ext == 'png' ) {
			$func = 'imagecreatefrompng';
		}
		return $func($src);
	}

	static function saveImage($imageObj, $filename, $quality=90) {
		$save_func = 'imagejpeg';
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ( $ext == 'gif' ) {
			$save_func = 'imagegif';
		} else if ( $ext == 'png' ) {
			$save_func = 'imagepng';
			if ( $quality > 9 ) {
				$quality = $quality / 10;
				$quality = round(10 - $quality);
			}
		}

		if ( $save_func === 'imagegif' ) {
			return $save_func($imageObj, $filename);
		}
		return $save_func($imageObj, $filename, $quality);
	}
}
