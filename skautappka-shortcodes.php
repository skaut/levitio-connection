<?php

if (!defined('ABSPATH')) {exit;}

function skautAppka_shortcode( $atts, $content = null )
{
	$args = shortcode_atts( array(
			'evidencni-cislo'=>'123',
			'staging'=>false,
			'zobraz'=>'vypravy',
			'title'=>'',
			'minule-vypravy'=>'ano',
			'budouci-vypravy'=>'ano',
			'isWidget' => false
		), $atts, "skautappka" );

	ob_start();
	the_widget( 'skautAppkaWidget', $args );
	$cd_code = ob_get_contents();
	ob_end_clean();

	return $cd_code;

}

add_shortcode( 'skautappka', 'skautAppka_shortcode');
