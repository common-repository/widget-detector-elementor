(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

     
$( window ).load(function() {

    $(window).on('elementor/frontend/init', function(){

        elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function( $scope ) {
            ///alert('test');
            //if ( $scope.data( 'shake' ) ){
            //    $scope.shake();
            //}

            wde_show_widgets_editor();
        } );

    });
    
    wde_show_widgets();
    wde_get_styles();

});

function wde_show_widgets_editor()
{
    $('.elementor-element.elementor-widget').each(function( index ) {
        //console.log( index + ": " + $( this ).text() );
        console.log($(this).find('.w_d_e_name').length);

        if($(this).find('.w_d_e_name').length > 0)return;

        var element_name = $(this).attr('class');

        //$(this).css('border', '1px dashed red');

        element_name = element_name.substr(element_name.lastIndexOf("elementor-widget-")+17);

        if(element_name.indexOf(" ") != -1)
            element_name = element_name.substr(0, element_name.indexOf(" "));

        if(element_name.indexOf("--") != -1)
            element_name = element_name.substr(0, element_name.indexOf("--"));

        var element_name_nice = wde_arr.get(element_name);

        if(element_name_nice)
            element_name = element_name_nice;
        
        $(this).append('<span class="w_d_e_name w_d_e_hidden">'+element_name+'</span>');

        $(this).on('mouseover', function(){
            $(this).find('span.w_d_e_name').removeClass('w_d_e_hidden');
        }).on("mouseout", function() {
            $(this).find('span.w_d_e_name').addClass('w_d_e_hidden');
        });

    });
}

function wde_show_widgets()
{
    $('.elementor-element.elementor-widget').each(function( index ) {
        //console.log( index + ": " + $( this ).text() );
        //console.log($(this).find('.w_d_e_name').length);

        if($(this).find('.w_d_e_name').length > 0)return;

        var element_name = $(this).attr('class');

        $(this).css('border', '1px dashed red');

        element_name = element_name.substr(element_name.lastIndexOf("elementor-widget-")+17);

        if(element_name.indexOf(" ") != -1)
            element_name = element_name.substr(0, element_name.indexOf(" "));

        if(element_name.indexOf("--") != -1)
            element_name = element_name.substr(0, element_name.indexOf("--"));

        var element_name_nice = wde_arr.get(element_name);

        if(element_name_nice)
            element_name = element_name_nice;
        
        $(this).append('<span class="w_d_e_name">'+element_name+'<span class="w_d_e_el_hint">i</span> <span class="w_d_e_el_copy_css"></span></span>');
        
    });
}


})( jQuery );

const wde_get_styles = () => {

    var generate_hint,generate_hint_all,generate_hint_clipboard,copy_style,detectColorAndShow;
    const cssProperties = [
        "background",
        "border",
        "box-shadow",
        "color",
        "cursor",
        "font-family",
        "font-size",
        "font-weight",
        "height",
        "letter-spacing",
        "margin",
        "max-height",
        "max-width",
        "opacity",
        "outline",
        "padding",
        "text-align",
        "transform",
        "transition",
        "width",
      ];

    const cssPropertiesParent = [
        "background",
        "border",
        "margin",
        "padding",
    ];

    jQuery('.w_d_e_el_hint').off().on('click', function(e){
        var hint_box = jQuery(this).find('.w_d_e_el_hint_box');
        if(hint_box.length && !hint_box.hasClass('w_d_e_hidden')) {
            jQuery('.w_d_e_el_hint_box').addClass('w_d_e_hidden');
            return false;
        }

        jQuery('.w_d_e_el_hint_box').addClass('w_d_e_hidden');
        jQuery('.w_d_e_el_hint_box').removeClass('top bottom left right');
        jQuery('.w_d_e_name').attr('style', '');

        if(!hint_box.length) {
            hint_box = jQuery('<span class="w_d_e_el_hint_box"></span>').appendTo(jQuery(this));
        }

        var elCoord = jQuery(this).get(0).getBoundingClientRect();
        if((+window.innerWidth - +elCoord.left) < +hint_box.outerWidth()+10) {
            hint_box.addClass('right');
        }
     
        if((+window.innerHeight - +elCoord.top) < +hint_box.outerHeight()+10) {
            hint_box.addClass('top');
        }

        if(jQuery(this).parent().parent().find('.elementor-widget-container').length) {

            var el = jQuery(this).parent().parent().find('.elementor-widget-container');
            if(this.parentElement.parentElement.classList.contains('elementor-section') || this.parentElement.parentElement.classList.contains('elementor-column')) {

            } else {
                if(el.find('.elementor-button-wrapper >*:first-child').length) {
                    el = el.find('.elementor-button-wrapper >*:first-child');
                } else if(el.find('>*:first-child').length) {
                    el = el.find('>*:first-child');
                } 
            }

            hint_box.html(generate_hint(el.get(0))).removeClass('w_d_e_hidden');
            hint_box.parent().parent().attr('style', 'z-index: 99999999999 !important;');
            copy_style(hint_box.find('.copy_style'));
        } else {
            jQuery('.w_d_e_el_hint_box').addClass('w_d_e_hidden');
        }
    })
      
    jQuery('.w_d_e_el_copy_css').off().on('click', function(e){
        if(jQuery(this).parent().parent().find('.elementor-widget-container').length) {
            var el = jQuery(this).parent().parent().find('.elementor-widget-container');
            if(this.parentElement.parentElement.classList.contains('elementor-section') || this.parentElement.parentElement.classList.contains('elementor-column')) {

            } else {
                if(el.find('.elementor-button-wrapper >*:first-child').length) {
                    el = el.find('.elementor-button-wrapper >*:first-child');
                } else if(el.find('>*:first-child').length) {
                    el = el.find('>*:first-child');
                } 
            }

            while (el && (el.tagName.toUpperCase() === 'STYLE' || el.tagName.toUpperCase() === 'SCRIPT')){
                el = el.next();
            }

            navigator.clipboard.writeText(generate_hint_clipboard(el.get(0)));
            wde_log_notify('Added in clipboard');
        } 
    })

    generate_hint_all = (el) => {
        var css_data = '';
        var css_obj = getComputedStyle(el);
        for (var i = 0; i < css_obj.length; i++) {
            css_data +=
                '<p><span class="opt">'+css_obj[i] + '</span> :' + css_obj
                    .getPropertyValue(css_obj[i])
                    + ';</p>';
        }
        return css_data;
    }

    generate_hint = (el) => {
       
        var css_data = '';
        var css_obj = getComputedStyle(el);
        css_data += '<h2 class="wde_hint_header">Inner element</h2>'; 

        for (const iterator of cssProperties) {
            if (css_obj.getPropertyValue(iterator) && css_obj.getPropertyValue(iterator) !== 'none') {
                css_data += '<p class="w_d_e_hint_line"><span class="opt">'+iterator+'</span><span class="value">: ' + detectColorAndShow(css_obj.getPropertyValue(iterator))+ ';<a href="#" class="copy_style" data-clipboard="' + (css_obj.getPropertyValue(iterator).replace(/\"/g, "'"))+ '"></a></span></p>'; 
            }
        }

        var css_obj = getComputedStyle(el.parentElement);
        css_data += '<h2 class="wde_hint_header_hr">Container Element</h2>'; 
        for (const iterator of cssPropertiesParent) {
            if (css_obj.getPropertyValue(iterator) && css_obj.getPropertyValue(iterator) !== 'none') {
                css_data += '<p class="w_d_e_hint_line"><span class="opt">'+iterator+'</span><span class="value">: ' + detectColorAndShow(css_obj.getPropertyValue(iterator))+ ';<a href="#" class="copy_style" data-clipboard="' + (css_obj.getPropertyValue(iterator).replace(/\"/g, "'"))+ '"></a></span></p>'; 
            }
        }
        
        return css_data;
    }

    copy_style = (selector) => {
        jQuery(selector).off().on('click', function(e){
            e.preventDefault(); 
            e.stopPropagation();
            navigator.clipboard.writeText(jQuery(this).attr('data-clipboard'));
            wde_log_notify('Added in clipboard');
        })
    }

    generate_hint_clipboard = (el) => {
       
        var css_data = '';
        var css_obj = getComputedStyle(el);

        for (const iterator of cssProperties) {
            if(css_obj.getPropertyValue(iterator) && css_obj.getPropertyValue(iterator) != 'none')
                css_data += ''+iterator+': ' + css_obj.getPropertyValue(iterator)+ ';\r\n'; 
        }
        
        return css_data;
    }

    detectColorAndShow = (text) => {
        const colorRegex = /#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})|rgb\((\d+),\s*(\d+),\s*(\d+)\)/g;
        let match;
        if ((match = colorRegex.exec(text)) !== null) {
            let color;
            if (match[1]) {
                color = match[0];
            } else {
                const r = match[2];
                const g = match[3];
                const b = match[4];
                color = `rgb(${r}, ${g}, ${b})`;
            }

            text += ' <span class="wde_color" style="background:'+color+'"></span>';
        }
        return text;
    };

}

const wde_log_notify = (text, type, popup_place) => {
	var $ = jQuery;
	if (!$('.wde_log_notify-box').length) $('body').append('<div class="wde_log_notify-box"></div>')
	if (typeof text == "undefined") var text = 'Undefined text';
	if (typeof type == "undefined") var type = 'success';
	if (typeof popup_place == "undefined") var popup_place = $('.wde_log_notify-box');
	var el_class = '';
	var el_timer = 5000;
	switch (type) {
		case "success":
			el_class = "success";
			break
		case "error":
			el_class = "error";
			break
		case "loading":
			el_class = "loading";
			el_timer = 2000;
			break
		default:
			el_class = "success";
			break
	}

	/* notify */
	var html = '';
	html = '<div class="wde_log_notify ' + el_class + '">\n\
				' + text + '\n\
		</div>';
	var notification = $(html).appendTo(popup_place).delay(100).queue(function() {
			$(this).addClass('show')
			setTimeout(function() {
				notification.removeClass('show')
				setTimeout(function() {
					notification.remove();
				}, 1000);
			}, el_timer);
		})
		/* end notify */
}
