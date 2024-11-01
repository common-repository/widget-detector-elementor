<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       elementinvader.com
 * @since      1.0.0
 *
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/public
 * @author     ElementInvader <support@elementinvader.com>
 */
class Widget_Detector_Elementor_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

        if(!function_exists('elementor_fail_php_version'))
        {
            return;
        }

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Widget_Detector_Elementor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Widget_Detector_Elementor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        if(isset($_GET['wde_show']) || (\Elementor\Plugin::$instance->preview->is_preview_mode() && get_option('wde_editor_hover', '0') == '1'))
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/widget-detector-elementor-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        if(!function_exists('elementor_fail_php_version'))
        {
            return;
        }

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Widget_Detector_Elementor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Widget_Detector_Elementor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        if(isset($_GET['wde_show']) || (\Elementor\Plugin::$instance->preview->is_preview_mode() && get_option('wde_editor_hover', '0') == '1'))
        {
            $elements_manager = \Elementor\Plugin::instance()->widgets_manager;
            $existing_widgets = $elements_manager->get_widget_types();
    
            $widgets_list = array();
    
            foreach($existing_widgets as $widget)
            {
                $category = '';
                if (isset($widget))
                {
                    $categories = $widget->get_categories();
    
                    if(isset($categories[0]))
                        $category = $categories[0];
                }

                if(!empty($category))$category = " ($category)";

                $widgets_list[$widget->get_name()] = $widget->get_title().$category;



            }

            ob_start();
            ?>
            <script>
            var wde_arr = new Map([
                <?php foreach($widgets_list as $key=>$val): ?>
                ['<?php echo esc_js($key); ?>', '<?php echo esc_js($val); ?>'],
                <?php endforeach; ?>
                ]);
            </script>
            <?php
            $contents = ob_get_clean();

            $contents = str_replace('<script>', '', $contents);
            $contents = str_replace('</script>', '', $contents);

            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/widget-detector-elementor-public.js', array( 'jquery' ), $this->version, false );
            wp_add_inline_script( $this->plugin_name, $contents );


        }
		

	}

    public function widgets_unregister()
    {
        if(!function_exists('elementor_fail_php_version'))
        {
            return;
        }

        $elementor = Elementor\Plugin::instance();
        
        if(is_admin()) // only enabled if admin exclude elementor editor
        {
            if ( ! $elementor->editor->is_edit_mode() ) {
                return;
            }
        }

        $widgets_exists = Elementor\Plugin::instance()->widgets_manager->get_widget_types();

        $elementor_widgets = array();
        foreach($widgets_exists as $widget)
        {
            $elementor_widgets[ $widget->get_name() ] = $widget->get_name();
        }

		$disabled_widgets = get_option( 'wde_disabled_widgets', array());
				
		if(!empty($disabled_widgets) && is_array($disabled_widgets)){			
			foreach ( $disabled_widgets as $key => $widget ) {
				$elementor->widgets_manager->unregister( $widget );
			}
		}
    }

}
