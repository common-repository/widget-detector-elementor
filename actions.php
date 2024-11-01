<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action('elementor/widgets/register', function($widgets_manager){

		$elementor = Elementor\Plugin::instance();
		if ( ! $elementor->editor->is_edit_mode() ) {
            return;
		}

		$selected_widgets = get_option( WDE_UNREGISTER_OPTION_KEY );
				
		if(!empty($selected_widgets)){			
			foreach ( $selected_widgets as $el_name => $val ) {
				//$elementor->widgets_manager->unregister( $el_name );
                $widgets_manager->unregister( $el_name );
			}
		}
},1000);


add_action( 'wp_enqueue_scripts', function(){
    wp_enqueue_style( 'widget-detector-elementor', WIDGET_DETECTOR_ELEMENTOR_URL . 'public/css/widget-detector-elementor-public.css', array(), 1, 'all' );

    $selected_widgets = get_option( WDE_HIDDER_OPTION_KEY );

    if(empty($selected_widgets)) return;
    $selected_widgets = array_keys($selected_widgets);
    array_walk($selected_widgets,function(&$item1){$item1 = '[data-widget_type="'.$item1.'.default"]';});
    $custom_css = join(',',$selected_widgets).'{display: none !important}';
    wp_add_inline_style( 'widget-detector-elementor', $custom_css );

} );