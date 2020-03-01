<?php
/*
* Plugin Name: SkautAppka Connection Plugin
* Plugin URI: https://www.skautappka.cz
* Description: Propojení SkautAppky s WordPressem.
* Version: 1.0.0
* Author: Luky Haraga
* Author URI: https://www.skautappka.cz
* Text Domain: skautappka-connection
*/

include_once('skautappka-util.php'); // Utility functions for backward compatibility
include_once('skautappka-shortcodes.php'); // Shortcode functions

global $skautappka_shortcode_ids;

/**
 * SkautAppka Widget Class
 */
class skautAppkaWidget extends WP_Widget {

    /** constructor */
    function __construct() {

		$widget_ops = array( 
			'classname' => 'skautAppkaWidget', 
			'description' => __( 'SkautAppka/widget' , 'skautappka-widget') 
		);
		
		parent::__construct( 
			'skautAppkaWidget', 
			__('SkautAppka Widget', 'skautappka-widget'), 
			$widget_ops 
		);

		$this->alt_option_name = 'skautappka_widget';

		add_action( 'admin_init', array(&$this, 'common_header'), 100, 1 );
		add_action( 'wp_enqueue_scripts', array(&$this, 'common_header'), 100, 1 );

		add_action( 'wp_footer', array(&$this, 'print_scripts'), 1000, 1 );
		add_action( 'wp_print_styles', array(&$this, 'print_styles'), 1000, 1 );
		add_action( 'admin_print_styles', array(&$this, 'print_styles'), 1000, 1 );

		$current_offset = get_option('gmt_offset');

		$this->pluginname = "SkautAppka Widget";
		$this->version = "1.0";
		$this->help_url = "https://www.skautappka.cz/";

		$this->settings_key = "skautappka_widget";
		$this->options_page = "skautappka-widget";

		// Include options array
		require_once("skautappka-options.php");
		$this->options = $options;
		$this->settings = $this->get_plugin_settings();

		add_action('admin_menu', array( &$this, 'admin_header') );

		$this->defaults = array(
			'evidencni-cislo'=>'',
			'title'=>'',
			'staging'=>false,
			'zobraz'=>'vypravy',
			'minule-vypravy'=>'ano',
			'budouci-vypravy'=>'ano',
		);
    }

    function widget($args, $instance) {
		global $post, $skautappka_shortcode_ids;

		//echo var_dump($args);

        extract( $args );

		$widget_options = wp_parse_args( $instance, $this->defaults );
		extract( $widget_options, EXTR_SKIP );


		if( !empty( $instance['staging'] ) ){ $staging = (bool) $staging; }

		// Get a new id
		$skautappka_shortcode_ids++;


		// If this is not a widget
		if( isset( $isWidget ) && false === $isWidget ){

			$style=" style=\"";

			if(!empty($bgcolor)){
				$style .= "background-color:".$bgcolor.";";
			}

			if(!empty($color)){ $style .=  " color:".$color. ";"; }
			if(!empty($width) && $width>0){ $style .= " width:".$width."px;"; }
			if(!empty($radius) && $radius>0){ $style .= " border-radius:".$radius."px;"; }
				$style .= " margin:0px auto; \"";

		}



			?>
				  <?php echo $before_widget; ?>
					<?php if ( $title )
							echo $before_title . $title . $after_title;
					?>

<div id="skautappka-widget-vypravy-<?= $skautappka_shortcode_ids ?>">

</div>
<div class="skautappka-widget-error" id="skautappka-widget-error-<?= $skautappka_shortcode_ids ?>">
	SkautAppka chyba: <span id="skautappka-widget-error-text-<?= $skautappka_shortcode_ids ?>"></span>
</div>
<script>
	(function($){$(document).ready(function($) {
		$.ajax({
			method: "GET",
			url: "<?= $staging ? "https://apistaging.skautappka.cz" : "https://api.skautappka.cz" ?>/neprihlasen/v1/akce/Výprava:<?php echo $instance["evidencni-cislo"] ?>?sort=ASC" ,
			dataType: "json"
		})
			.done(function (msg) {
				var now = Date.now();
				//var nejblizsiVyrenderovana = false;
				for (var i = 0; i < msg.Items.length; i++)
				{
					var vyprava = msg.Items[i];

					if (vyprava.Konec < now && vyprava.Stav !== "Veřejný")
						continue;

					if ("<?= $instance["minule-vypravy"] ?>" === "ne" && vyprava.Konec < now)
						continue;

					if ("<?= $instance["budouci-vypravy"] ?>" === "ne" && vyprava.Konec > now)
						continue;

					skautAppkaUpdateVyprava(
						$, 
						$('#skautappka-widget-vypravy-<?= $skautappka_shortcode_ids ?>'),
						!(vyprava.Konec > now && vyprava.Stav === "Veřejný"),
						msg.Items[i],
						"<?= $skautappka_shortcode_ids ?>-" + i);
						
					// if (vyprava.Konec > now && !nejblizsiVyrenderovana)
					// 	nejblizsiVyrenderovana = true;
				}

			})
			.fail(function (er) {
				$("#skautappka-widget-<?= $skautappka_shortcode_ids ?>").hide();

				$("#skautappka-widget-error-text-<?= $skautappka_shortcode_ids ?>").text(er.responseText);
				$("#skautappka-widget-error-<?= $skautappka_shortcode_ids ?>").show();
			});
		}); })(jQuery);
</script>


				  <?php echo $after_widget; ?>
			<?php

    }

    function update( $new_instance, $old_instance ) {
        return $new_instance;
    }

    function form($instance) {

		global $post, $skautappka_shortcode_ids;

		$widget_options = wp_parse_args( $instance, $this->defaults );
		extract( $widget_options, EXTR_SKIP );

		$skautappka_shortcode_ids++;

		if( !empty( $instance['staging'] ) ){ $staging = (bool) $staging; }
        ?>

		<p>
			<label for="<?php echo $this->get_field_id('evidencni-cislo'); ?>"><?php _e('Evidenční číslo:', 'evidencni-cislo'); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id('evidencni-cislo'); ?>" name="<?php echo $this->get_field_name('evidencni-cislo'); ?>" type="text" value="<?php echo $widget_options["evidencni-cislo"]; ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Zobraz:', 'zobraz'); ?> 
				<input disabled class="widefat" id="<?php echo $this->get_field_id('zobraz'); ?>" name="<?php echo $this->get_field_name('zobraz'); ?>" type="text" value="<?php echo $widget_options["zobraz"]; ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('minule-vypravy'); ?>"> <?php _e('Minulé výpravy:', 'minule-vypravy'); ?></label>
			<select name="<?php echo $this->get_field_name('minule-vypravy'); ?>" id="<?php echo $this->get_field_id('minule-vypravy'); ?>" >
				<option value="ano" <?php if($widget_options["minule-vypravy"] == "ano") { ?> selected="selected" <?php } ?>>Ano</option>
				<option value="ne" <?php if($widget_options["minule-vypravy"] == "ne") { ?> selected="selected" <?php } ?>>Ne</option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('budouci-vypravy'); ?>"> <?php _e('Budoucí výpravy:', 'budouci-vypravy'); ?></label>
			<select name="<?php echo $this->get_field_name('budouci-vypravy'); ?>" id="<?php echo $this->get_field_id('budouci-vypravy'); ?>" >
				<option value="ano" <?php if($widget_options["budouci-vypravy"] == "ano") { ?> selected="selected" <?php } ?>>Ano</option>
				<option value="ne" <?php if($widget_options["budouci-vypravy"] == "ne") { ?> selected="selected" <?php } ?>>Ne</option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('Staging'); ?>"><?php _e('Staging:', 'staging'); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id('staging'); ?>" name="<?php echo $this->get_field_name('staging'); ?>" type="checkbox" <?php checked( $staging ); ?> />
			</label>
			<br/>
			<span style="color: gray; font-style: italic">(STAGING je nastavení pro testery Appky - nezapínejte - plugin vám nebude fungovat)</span>
		</p>

		<!-- <p><a href="options-general.php?page=skautappka-widget" target="_blank">Jak pou..</a></p> -->

<div class="clear"></div>
        <?php

	}



	function common_header( $instance ){
		if( !wp_doing_ajax() ){

			wp_enqueue_script('jquery');
		}
	}

  function print_scripts( $instance = null ){
    if( !wp_doing_ajax() ){
      //$cd_settings = $this->get_plugin_settings();
    ?>
	
			<script type='text/javascript'>
			function skautAppkaUpdateVyprava(jq, parent, isCollapsed, vyprava, suffix)
			{
				var vypravaHtml = '';
				
				if (isCollapsed)
					vypravaHtml += '<h3 class="skautappka-widget-collapsed"><span id="skautappka-widget-nazev-vypravy-' + suffix + '">Výlet do Bruntálu </span><span class="skautappka-widget-nadpis-doplnek" id="skautappka-widget-nadpis-doplnek-' + suffix + '"></span></h3>';
				else
					vypravaHtml += '<h2><span id="skautappka-widget-nazev-vypravy-' + suffix + '">Výlet do Bruntálu </span><span class="skautappka-widget-nadpis-doplnek" id="skautappka-widget-nadpis-doplnek-' + suffix + '"></span></h2>';

				vypravaHtml += '<div id="skautappka-widget-vyprava-info-' + suffix + ' class="skautappka-widget-vyprava-info" style="'+ (isCollapsed ? 'display: none;' : '') + '">';
				vypravaHtml += '<div>';
				vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Sraz:</span> <span class="skautappka-widget-sraz-datum" id="skautappka-widget-sraz-datum-' + suffix + '"></span><span class="skautappka-widget-sraz-cas" id="skautappka-widget-sraz-cas-' + suffix + '"></span><span class="skautappka-widget-sraz-misto" id="skautappka-widget-sraz-misto-' + suffix + '"></span><span class="skautappka-widget-sraz-zpusob-dopravy" id="skautappka-widget-sraz-zpusob-dopravy-' + suffix + '"></span><br/>';
				vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Návrat:</span> <span class="skautappka-widget-navrat-datum" id="skautappka-widget-navrat-datum-' + suffix + '"></span><span class="skautappka-widget-navrat-cas" id="skautappka-widget-navrat-cas-' + suffix + '"></span><span class="skautappka-widget-navrat-misto" id="skautappka-widget-navrat-misto-' + suffix + '"></span><span class="skautappka-widget-navrat-zpusob-dopravy" id="skautappka-widget-navrat-zpusob-dopravy-' + suffix + '"></span><br/>';
				vypravaHtml += '</div>';
				vypravaHtml += '<div class="skautappka-widget-sekce-cena" id="skautappka-widget-sekce-cena-' + suffix + '">';
				vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Cena:</span> <span class="skautappka-widget-cena" id="skautappka-widget-cena-' + suffix + '"></span><span class="skautappka-widget-poznamka-k-cene" id="skautappka-widget-poznamka-k-cene-' + suffix + '"></span>';
				vypravaHtml += '</div>';
				vypravaHtml += '<div class="skautappka-widget-s-sebou" id="skautappka-widget-sekce-s-sebou-' + suffix + '">';
				vypravaHtml += '<span class="skautappka-widget-nazev-polozky">S sebou:</span>';
				vypravaHtml += '<div class="skautappka-widget-s-sebou-text" id="skautappka-widget-s-sebou-text-' + suffix + '"></div>';
				vypravaHtml += '</div>';
				vypravaHtml += '<div class="skautappka-widget-sekce-poznamky" id="skautappka-widget-sekce-poznamky-' + suffix + '">';
				vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Další informace:</span>';
				vypravaHtml += '<div class="skautappka-widget-poznamky-text" id="skautappka-widget-poznamky-text-' + suffix + '"></div>';
				vypravaHtml += '</div>';
				vypravaHtml += '<div class="skautappka-widget-sekce-kontakt" id="skautappka-widget-sekce-kontakt-' + suffix + '">';
				vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Kontakt:</span> <span class="skautappka-widget-kontakt" id="skautappka-widget-kontakt-' + suffix + '"></span>';
				vypravaHtml += '</div>';
				vypravaHtml += '</div>';
				vypravaHtml += '<div class="skautappka-widget-divider"></div>';

				parent.append(vypravaHtml);

				jq("#skautappka-widget-nazev-vypravy-" + suffix).text(vyprava.Nazev !== null && vyprava.Nazev !== undefined ? vyprava.Nazev : "Výprava");
	
				jq("#skautappka-widget-sraz-datum-" + suffix).text(new Date(vyprava.Zacatek).toLocaleDateString("cs-CS"));
				jq("#skautappka-widget-sraz-cas-" + suffix).text(new Date(vyprava.Zacatek).toLocaleTimeString("cs-CS"));

				jq("#skautappka-widget-navrat-datum-" + suffix).text(new Date(vyprava.Konec).toLocaleDateString("cs-CS"));
				jq("#skautappka-widget-navrat-cas-" + suffix).text(new Date(vyprava.Konec).toLocaleTimeString("cs-CS"));

				if (isCollapsed)
				{
					var sraz = jq("#skautappka-widget-sraz-datum-" + suffix).text();
					var navrat = jq("#skautappka-widget-navrat-datum-" + suffix).text();
					jq("#skautappka-widget-nadpis-doplnek-" + suffix).text(sraz !== navrat ? sraz + " - " + navrat : sraz);
				}
				else
				{
					if (vyprava.Stav === "Koncept")
						jq("#skautappka-widget-nadpis-doplnek-" + suffix).text("Výprava se připravuje");
				}

				if (vyprava.Info !== undefined)
				{
					jq("#skautappka-widget-sraz-misto-" + suffix).text(vyprava.Info.MistoSrazu);
					jq("#skautappka-widget-sraz-zpusob-dopravy-" + suffix).text(vyprava.Info.ZpusobDopravySrazu);

					jq("#skautappka-widget-navrat-misto-" + suffix).text(vyprava.Info.MistoNavratu);
					jq("#skautappka-widget-navrat-zpusob-dopravy-" + suffix).text(vyprava.Info.ZpusobDopravyNavratu);
					
					if ((vyprava.Info.Cena === undefined || vyprava.Info.Cena === null) && (vyprava.Info.PoznamkaKCene === undefined || vyprava.Info.PoznamkaKCene === null))
					{
						jq("#skautappka-widget-sekce-cena-" + suffix).hide();
					}
					else
					{
						
						if (vyprava.Info.Cena === undefined || vyprava.Info.Cena === null)
							jq("#skautappka-widget-cena-" + suffix).hide();
						else
							jq("#skautappka-widget-cena-" + suffix).text(vyprava.Info.Cena);

						if (vyprava.Info.PoznamkaKCene === undefined || vyprava.Info.PoznamkaKCene === null)
							jq("#skautappka-widget-poznamka-k-cene-" + suffix).hide();
						else
							jq("#skautappka-widget-poznamka-k-cene-" + suffix).text(vyprava.Info.PoznamkaKCene);

						jq("#skautappka-widget-sekce-cena-" + suffix).show();
					}

					if (vyprava.Info.VeciSSebou === undefined || vyprava.Info.VeciSSebou === null)
						jq("#skautappka-widget-sekce-s-sebou-" + suffix).hide();
					else
						jq("#skautappka-widget-s-sebou-text-" + suffix).html(vyprava.Info.VeciSSebou);

					if (vyprava.Info.Poznamky === undefined || vyprava.Info.Poznamky === null)
						jq("#skautappka-widget-sekce-poznamky-" + suffix).hide();
					else
						jq("#skautappka-widget-poznamky-text-" + suffix).html(vyprava.Info.Poznamky);

					if (vyprava.Info.Kontakt === undefined || vyprava.Info.Kontakt === null)
						jq("#skautappka-widget-sekce-kontakt-" + suffix).hide();
					else
						jq("#skautappka-widget-kontakt-" + suffix).html(vyprava.Info.Kontakt);
				}
				else
				{
					jq("#skautappka-widget-sekce-cena-" + suffix).hide();
					jq("#skautappka-widget-sekce-s-sebou-" + suffix).hide();
					jq("#skautappka-widget-sekce-poznamky-" + suffix).hide();
					jq("#skautappka-widget-sekce-kontakt-" + suffix).hide();
					jq("#skautappka-widget-sraz-cas-" + suffix).hide();
					jq("#skautappka-widget-navrat-cas-" + suffix).hide();
				}


				jq("#skautappka-widget-" + suffix).show();
				jq("#skautappka-widget-error-" + suffix).hide();
			}
		</script>
		<?php
    }
  }

	function admin_header( $instance ){
		if( !wp_doing_ajax() ){

			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_style('jquery-ui-custom-css', plugins_url('css/jquery-ui.min.css', __FILE__));

      /* If we are on options page */
      if ( @$_GET['page'] == $this->options_page ) {

        wp_enqueue_style( "countdown-widget-admin", plugins_url( '/css/countdown-admin.css' , __FILE__ ) , false, null, "all");

    		if ( @$_REQUEST['action'] && 'save' == $_REQUEST['action'] ) {

    			// Save settings
    			$settings = $this->get_settings();

    			// Set updated values
    			foreach( $this->options as $option ){
    				if( array_key_exists( 'id', $option ) ){
              if( $option['type'] == 'checkbox' && empty( $_REQUEST[ $option['id'] ] ) ) {
    						$settings[ $option['id'] ] = 'off';
    					} elseif( array_key_exists( $option['id'], $_REQUEST ) ) {
    						$settings[ $option['id'] ] = $_REQUEST[ $option['id'] ];
    					} else {
                // hmm no key here?
              }
    				}
    			}

    			// Save the settings
    			update_option( $this->settings_key, $settings );
    			header("Location: admin.php?page=" . $this->options_page . "&saved=true&message=1");
    			die;
    		} else if( @$_REQUEST['action'] && 'reset' == $_REQUEST['action'] ) {

    			// Start a new settings array
    			$settings = array();
    			delete_option( $this->settings_key );

    			header("Location: admin.php?page=" . $this->options_page . "&reset=true&message=2");
    			die;
    		}

    	}

    	$page = add_options_page(
    		__('WordPress SkautAppka Widget', 'skautappka-widget'),
    		__('SkautAppka Plugin', 'skautappka-widget'),
    		'manage_options',
    		$this->options_page,
    		array( &$this, 'options_page')
    	);
		}
	}

  function get_plugin_settings(){
  	$settings = get_option( $this->settings_key );

  	if(FALSE === $settings){
  		// Options doesn't exist, install standard settings
  		return $this->install_default_settings();
  	} else { // Options exist, update if necessary
  		if( !empty( $settings['version'] ) ){ $ver = $settings['version']; }
  		else { $ver = ''; }

  		if($ver != $this->version){
  			// Update settings
  			return $this->update_plugin_settings( $settings );
  		} else {
  			// Plugin is up to date, let's return
  			return $settings;
  		}
  	}
  }

  /* Updates a single option key */
  function update_plugin_setting( $key, $value ){
  	$settings = $this->get_plugin_settings();
  	$settings[$key] = $value;
  	update_option( $this->settings_key, $settings );
  }

  /* Retrieves a single option */
  function get_plugin_setting( $key, $default = '' ) {
  	$settings = $this->get_plugin_settings();
  	if( array_key_exists($key, $settings) ){
  		return $settings[$key];
  	} else {
  		return $default;
  	}

  	return FALSE;
  }

  function install_default_settings(){
  	// Create settings array
  	$settings = array();

  	// Set default values
  	foreach($this->options as $option){
  		if( array_key_exists( 'id', $option ) && array_key_exists( 'std', $option ) )
  			$settings[ $option['id'] ] = $option['std'];
  	}

  	$settings['version'] = $this->version;
  	// Save the settings
  	update_option( $this->settings_key, $settings );
  	return $settings;
  }

  function update_plugin_settings( $current_settings ){
  	//Add missing keys
  	foreach($this->options as $option){
  		if( array_key_exists ( 'id' , $option ) && !array_key_exists ( $option['id'] ,$current_settings ) ){
  			$current_settings[ $option['id'] ] = $option['std'];
  		}
  	}

  	update_option( $this->settings_key, $current_settings );
  	return $current_settings;
  }

  function options_page(){
  	global $options, $current;

  	$title = "SkautAppka Plugin Options";

  	$options = $this->options;
  	$current = $this->get_plugin_settings();

  	$messages = array(
  		"1" => __("Settings are saved.", "countdown-widget"),
  		"2" => __("Settings are reset.", "countdown-widget")
  	);

  	include_once( "skautappka-options-page.php" );

  }

	function print_styles( $instance ){
		$all_widgets = $this->get_settings();

?>
<style type="text/css">

.skautappka-widget-sraz-datum
{
	margin-right: 5px;
	font-weight: bold;
}

.skautappka-widget-sraz-cas
{
	margin-right: 5px;
}

.skautappka-widget-sraz-misto
{
	margin-right: 5px;
	font-weight: bold;
}
/* .skautappka-widget-sraz-zpusob-dopravy{} */

.skautappka-widget-navrat-datum
{
	margin-right: 5px;
	font-weight: bold;
}

.skautappka-widget-navrat-cas
{
	margin-right: 5px;
}

.skautappka-widget-navrat-misto
{
	margin-right: 5px;
	font-weight: bold;
}

/* .skautappka-widget-navrat-zpusob-dopravy{} */

.skautappka-widget-cena
{
	margin-right: 5px;
	font-weight: bold;
}

/* .skautappka-widget-poznamka-k-cene
{
	margin-right: 5px;
} */


.skautappka-widget-nazev-polozky
{
	text-decoration: underline;
	font-weight: bold;
}
.skautappka-widget-error
{
	color: red;
	background-color: white;
	display: none;
}
.skautappka-widget-body div
{
	margin-bottom: 10px;
}
.skautappka-widget-body
{
	display: none;
}
.skautappka-widget-s-sebou p
{
	margin-top: 0px !important;
	margin-bottom: 0px !important;
}
.skautappka-widget-poznamky-text p
{
	margin-top: 0px !important;
	margin-bottom: 0px !important;
}
.skautappka-widget-nadpis-doplnek
{
	font-size: 60%;
	margin-left: 10px;
}
.skautappka-widget-divider {
	border-bottom: 1px solid gray;
	margin-top: 5px;
	margin-bottom: 5px;
}
.skautappka-widget-collapsed {
	opacity: 50%;
	margin-top: 15px;
	margin-bottom: 15px;
}
.skautappka-widget-sekce-cena {
	margin-top: 10px;
	margin-bottom: 10px;
}
.skautappka-widget-sekce-poznamky {
	margin-top: 10px;
	margin-bottom: 10px;
}
.skautappka-widget-sekce-kontakt {
	margin-top: 10px;
	margin-bottom: 10px;
}
<?php

	echo "</style>\n";


	}

	function header( $instance = null ){}
	function footer( $instance = null ){}


} // class shailan_CountdownWidget

// register widget
function skautappka_register_widget(){
	return register_widget("skautAppkaWidget");
}
add_action( 'widgets_init', 'skautappka_register_widget' );

// Settings link
function skautappka_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=skautappka-widget">Jak na to?</a>';
    array_push( $links, $settings_link );
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'skautappka_add_settings_link' );
