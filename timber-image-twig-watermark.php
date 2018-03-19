<?php
/**
 * Plugin Name:     Timber Image Twig Watermark
 * Plugin URI:      Gusmanson.nl
 * Description:     Add a basic image-watermarking Twig filter for use with Timber
 * Author:          Gusmanson
 * Author URI:      Gusmanson.nl
 * Text Domain:     timber-image-twig-watermark
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         timber_image_twig_watermark
 */

namespace Gusmanson;

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

add_action('plugins_loaded', function() {

	if ( ! class_exists( '\Timber' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
		});

		return;
	}

	// Include the Timber compatible image operation
	include 'WatermarkTwigfilterHelper.php';

	// Add the filter to Twig! It can just call ImageHelper,
	// if it knows the operation it will perform it. If it does not know it,
	// it will return the source image.
	add_action('timber/twig/filters', function( $twig ) {
		$twig->addFilter(new \Twig_SimpleFilter('watermark',
			['\Gusmanson\WatermarkTwigfilterHelper', 'watermark']
		));
		return $twig;
	});
});
