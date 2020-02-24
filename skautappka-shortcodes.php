<?php

function skautAppka_shortcode( $atts, $content = null ){
	global $post, $subpages_indexes;

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

} add_shortcode( 'skautappka', 'skautAppka_shortcode');

// function skautAppkaWidget_shortcode_up( $atts, $content = null ){

// 	$atts['direction'] = 'up';
// 	return skautAppka_shortcode( $atts, $content );

// } add_shortcode( 'countup', 'skautAppkaWidget_shortcode_up');
