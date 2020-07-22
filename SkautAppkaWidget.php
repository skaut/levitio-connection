<?php

global $skautappka_shortcode_ids;

/**
 * SkautAppka Widget Class
 */
class SkautAppkaWidget extends WP_Widget
{
    private $defaults = [
        'evidencni-cislo' => '',
        'title' => '',
        'staging' => false,
        'zobraz' => 'vypravy',
        'minule-vypravy' => 'ano',
        'budouci-vypravy' => 'ano',
    ];

    public $pluginname = "SkautAppka Widget";
    public $version = "1.0";
    public $help_url = "https://www.skautappka.cz/";
    public $settings_key = "skautappka_widget";
    public $options_page = "skautappka-widget";
    public $options = [];
    public $settings;


    /** constructor */
    public function __construct()
    {
        $widget_ops = [
            'classname' => 'SkautAppkaWidget',
            'description' => __( 'SkautAppka/widget' , 'skautappka-widget')
        ];

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

        $this->settings = $this->get_plugin_settings();

        add_action('admin_menu', array( &$this, 'admin_header') );

    }


    /**
     * @param $args
     * @return array
     */
    private function getVaidatedArgs($args)
    {
        // WP vec na doplneni default hodnot // (nakonec zavola prosty array_merge)
        $args = wp_parse_args($args, $this->defaults);

        return [
            'evidencni-cislo'   => (string) ($args['evidencni-cislo'] ?: ''),
            'title'             => (string) ($args['title'] ?: ''),
            'zobraz'            => (string) ($args['zobraz'] ?: 'vypravy'),
            'minule-vypravy'    => (string) ($args['minule-vypravy'] ?: 'ano'),
            'budouci-vypravy'   => (string) ($args['budouci-vypravy'] ?: 'ano'),
            'staging'           => (bool) ($args['staging'] ?: false),
        ];
    }


    /**
     * @param $beforeWidget
     * @param $beforeTitle
     * @param $afterTitle
     * @param string $title
     * @return string
     */
    private function getBeforeWidgetHtml($beforeWidget, $beforeTitle, $afterTitle, $title = '')
    {
        $resultHtml = $beforeWidget;

        if (!empty($title))
        {
            $resultHtml .= $beforeTitle . $title . $afterTitle;
        }

        return $resultHtml;
    }


    /**
     * @param $fullApiUrl
     * @param $skautappka_shortcode_ids
     * @param $minuleVypravy
     * @param $budouciVypravy
     * @return string
     */
    private function getScriptHtml($fullApiUrl, $skautappka_shortcode_ids, $minuleVypravy, $budouciVypravy)
    {
        return '<script>
			(function($){$(document).ready(function($) {
				$.ajax({
					method: "GET",
					url: "'.esc_js($fullApiUrl).'" ,
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

							if ("'.esc_js($minuleVypravy).'" === "ne" && vyprava.Konec < now)
								continue;

							if ("'.esc_js($budouciVypravy).'" === "ne" && vyprava.Konec > now)
								continue;

							skautAppkaUpdateVyprava(
								$,
								$(\'#skautappka-widget-vypravy-'.esc_js($skautappka_shortcode_ids).'\'),
								!(vyprava.Konec > now && vyprava.Stav === "Veřejný"),
								msg.Items[i],
								"'.esc_js($skautappka_shortcode_ids).'-" + i);

							// if (vyprava.Konec > now && !nejblizsiVyrenderovana)
							// 	nejblizsiVyrenderovana = true;
						}

					})
					.fail(function (er) {
						var skautappka_shortcode_ids = '.esc_js($skautappka_shortcode_ids).';
						$("#skautappka-widget-"+skautappka_shortcode_ids).hide();

						$("#skautappka-widget-error-text-"+skautappka_shortcode_ids).text(er.responseText);
						$("#skautappka-widget-error-"+skautappka_shortcode_ids).show();
					});
			}); })(jQuery);
		</script>';
    }


    /**
     * @param $instanceArgs
     * @param $skautappka_shortcode_ids
     * @return string
     */
    private function getWidgetHtml($instanceArgs, $skautappka_shortcode_ids)
    {
        $escShortcodeIds = esc_html($skautappka_shortcode_ids);

        $resultHtml = '<div id="skautappka-widget-vypravy-' . $escShortcodeIds . '"></div>';
        $resultHtml .= '<div class="skautappka-widget-error" id="skautappka-widget-error-' . $escShortcodeIds . '">
			SkautAppka chyba: <span id="skautappka-widget-error-text-' . $escShortcodeIds . '"></span>
		</div>';

        $apiUrlDomain = 'https://api.skautappka.cz';

        if ($instanceArgs['staging'])
        {
            $apiUrlDomain = 'https://apistaging.skautappka.cz';
        }

        $fullApiUrl = $apiUrlDomain . '/neprihlasen/v1/akce/Výprava:' . esc_js($instanceArgs["evidencni-cislo"]) . '?sort=ASC';

        $resultHtml .= $this->getScriptHtml($fullApiUrl, $escShortcodeIds, $instanceArgs["minule-vypravy"], $instanceArgs["budouci-vypravy"]);

        return $resultHtml;
    }


    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        global $skautappka_shortcode_ids;


        $instanceArgs = $this->getVaidatedArgs($instance);

        // Get a new id
        $skautappka_shortcode_ids++;

        $outputHTML = $this->getBeforeWidgetHtml($args['before_widget'], $args['before_title'], $args['after_title'], $instanceArgs['title']);
        $outputHTML .= $this->getWidgetHtml($instanceArgs, $skautappka_shortcode_ids);
        $outputHTML .= $args['after_widget'];

        echo $outputHTML;
    }


    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        return $new_instance;
    }


    /**
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        global $skautappka_shortcode_ids;

        $instanceArgs = $this->getVaidatedArgs($instance);

        $skautappka_shortcode_ids++;

        $minuleVypravyAno = $instanceArgs["minule-vypravy"] === "ano" ? 'selected="selected"' : '';
        $minuleVypravyNe= $instanceArgs["minule-vypravy"] === "ano" ? 'selected="selected"' : '';
        $budouciVypravyAno = $instanceArgs["minule-vypravy"] === "ano" ? 'selected="selected"' : '';
        $budouciVypravyNe= $instanceArgs["minule-vypravy"] === "ano" ? 'selected="selected"' : '';

        $outputHtml = '
            <p>
                <label for="'.esc_html($this->get_field_id('evidencni-cislo')).'">'._e('Evidenční číslo:', 'evidencni-cislo').'
                    <input class="widefat"
                        id="'.esc_html($this->get_field_id('evidencni-cislo')).'"
                        name="'.esc_html($this->get_field_name('evidencni-cislo')).'>"
                        type="text"
                        value="'.esc_html($instanceArgs["evidencni-cislo"]).'"
                    />
                </label>
            </p>
    
            <p>
                <label for="'.esc_html($this->get_field_id('title')).'">'._e('Zobraz:', 'zobraz').'
                    <input disabled class="widefat"
                        id="'.esc_html($this->get_field_id('zobraz')).'" 
                        name="'.esc_html($this->get_field_name('zobraz')).'" type="text"
                        value="'.$instanceArgs["zobraz"].'" 
                    />
                </label>
            </p>
    
            <p>
                <label for="'.esc_html($this->get_field_id('minule-vypravy')).'">
                     '._e('Minulé výpravy:', 'minule-vypravy').'
                 </label>
                <select name="'.$this->get_field_name('minule-vypravy').'"
                    id="'.$this->get_field_id('minule-vypravy').'"
                >
                    <option value="ano" '.esc_html($minuleVypravyAno).'>Ano</option>
                    <option value="ne" '.esc_html($minuleVypravyNe).'>Ne</option>
                </select>
            </p>
    
            <p>
                <label for="'.esc_html($this->get_field_id('budouci-vypravy')).'">
                    '._e('Budoucí výpravy:', 'budouci-vypravy').'
                </label>
                <select name="'.esc_html($this->get_field_name('budouci-vypravy')).'"
                    id="'.esc_html($this->get_field_id('budouci-vypravy')).'" >
                    <option value="ano" '.esc_html($budouciVypravyAno).'>Ano</option>
                    <option value="ne" '.esc_html($budouciVypravyNe).'>Ne</option>
                </select>
            </p>
    
            <p>
                <label for="'.esc_html($this->get_field_id('Staging')).'">'._e('Staging:', 'staging').'
                    <input class="widefat"
                        id="'.esc_html($this->get_field_id('staging')).'"
                        name="'.esc_html($this->get_field_name('staging')).'"
                        type="checkbox"
                        '. checked($instanceArgs['staging']).'
                     />
                </label>
                <br/>
                <span style="color: gray; font-style: italic">(STAGING je nastavení pro testery Appky - nezapínejte - plugin vám nebude fungovat)</span>
            </p>
    
            <!-- <p><a href="options-general.php?page=skautappka-widget" target="_blank">Jak pou..</a></p> -->
    
            <div class="clear"></div>
        ';

        echo $outputHtml;
    }


    /**
     * @param $instance
     */
    function common_header($instance)
    {
        if (!wp_doing_ajax()) {
            wp_enqueue_script('jquery');
        }
    }

    /**
     * @param null $instance
     */
    function print_scripts($instance = null)
    {
            if (!wp_doing_ajax()) {
            echo '<script type="text/javascript">'
                . file_get_contents(plugins_url('updateVyprava.js', __FILE__))
                . '</script>';
        }
    }


    /**
     * @param $instance
     */
    function admin_header($instance)
     {
        if (!wp_doing_ajax()) {
            add_options_page(
                __('WordPress SkautAppka Widget', 'skautappka-widget'),
                __('SkautAppka Plugin', 'skautappka-widget'),
                'manage_options',
                $this->options_page,
                [ &$this, 'options_page']
            );
        }
    }


    /**
     * @return array|bool|mixed|void
     */
    private function get_plugin_settings()
    {
        $settings = get_option($this->settings_key);

        if (FALSE === $settings) {
            // Options doesn't exist, install standard settings
            return $this->install_default_settings();
        } else { // Options exist, update if necessary
            if (!empty($settings['version'])) {
                $ver = $settings['version'];
            } else {
                $ver = '';
            }

            if ($ver != $this->version) {
                // Update settings
                return $this->update_plugin_settings( $settings );
            } else {
                // Plugin is up to date, let's return
                return $settings;
            }
        }
    }


    /**
     * Updates a single option key
     *
     * @param $key
     * @param $value
     */
    public function update_plugin_setting($key, $value)
    {
        $settings = $this->get_plugin_settings();
        $settings[$key] = $value;
        update_option($this->settings_key, $settings);
    }


    /**
     * Retrieves a single option
     *
     * @param $key
     * @param string $default
     * @return mixed|string
     */
    public function get_plugin_setting($key, $default = '') {
        $settings = $this->get_plugin_settings();
        if(array_key_exists($key, $settings)) {
            return $settings[$key];
        } else {
            return $default;
        }
    }


    /**
     * @return array
     */
    function install_default_settings()
    {
        // Create settings array
        $settings = [];

        // Set default values
        foreach($this->options as $option) {
            if(array_key_exists('id', $option) && array_key_exists('std', $option)) {
                $settings[$option['id']] = $option['std'];
            }
        }

        $settings['version'] = $this->version;
        // Save the settings
        update_option($this->settings_key, $settings);
        return $settings;
    }

    /**
     * @param $current_settings
     * @return mixed
     */
    function update_plugin_settings($current_settings)
    {
        //Add missing keys
        foreach($this->options as $option) {
            if(array_key_exists ('id' , $option) && !array_key_exists ($option['id'] ,$current_settings)) {
                $current_settings[$option['id']] = $option['std'];
            }
        }

        update_option($this->settings_key, $current_settings);
        return $current_settings;
    }


    /**
     * @return string
     */
    private function getNotificationBlockHtml()
    {
        $messages = array(
            "1" => __("Settings are saved.", "countdown-widget"),
            "2" => __("Settings are reset.", "countdown-widget")
        );

        $outputHtml = '<div id="notifications">';

        if (isset($_GET['message']) && isset($messages[$_GET['message']]))
        {
                $outputHtml .= '<div id="message" class="updated fade"><p>'.esc_html($messages[$_GET['message']]).'</p>';
        }

        $outputHtml .= '</div>';

        return $outputHtml;
    }


    /**
     * @return string
     */
    private function getDebugBlockHtml()
    {
        $current = $this->get_plugin_settings();

        $debugInfo = '';

        if(WP_DEBUG)
        {
         $debugInfo = '<h3>Debug information</h3>
            <p>You are seeing this because your WP_DEBUG variable is set to true.</p>
            <pre>'.esc_html(print_r($current)).'</pre>';
        }

        return '<div id="debug-info">'
            . $debugInfo
            . '</div><!-- /debug-info -->';;
    }


    /**
     * Options Page html
     */
    public function options_page()
    {
        global $options;

        $options = $this->options;

        $outputHtml = '<div class="wrap options-page">
	        <h2>SkautAppka Plugin Options</h2>
            <div class="nav">
                <a class="nav-link" href="https://www.skautappka.cz/">SkautAppka Web</a>
            </div>';

        $outputHtml .= $this->getNotificationBlockHtml();
        $outputHtml .= file_get_contents(plugins_url('options-page.html', __FILE__));
        $outputHtml .= $this->getDebugBlockHtml();
        $outputHtml .= '</div>';

        echo $outputHtml;
    }


    /**
     * @param $instance
     */
    public function print_styles($instance)
    {
        echo '<style type="text/css">
            '.file_get_contents(plugins_url('widget_styles.css', __FILE__)).'
             </style>
        ';
    }
}
