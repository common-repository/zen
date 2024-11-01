;(function($) {
	// TODO: Refactor things!

	jQuery(document).ready(function() {
		
		jQuery('#wpwrap').css('position', 'relative');
		
		zen.init();
		
		jQuery('<a/>')
			.attr('href', '#zen')
			.text('zen')
			.appendTo(
				jQuery('<div/>')
					.addClass('postbox')
					.addClass('zen-activate')
					.prependTo(jQuery('#side-sortables'))
				)
			;
		
		jQuery(document).ajaxSend(function(e, x, a) {
			if( typeof(zen.current_theme.slug) !== 'undefined' && zen.current_theme.slug != '' )
				a.data += '&' + jQuery.param( {active_theme: zen.current_theme.slug} );
		})
		
		jQuery('.zen-activate').click(zen.omm);
		jQuery('.zen-deactivate').click(zen.unomm);
		
		if( parseInt(zen_options.onload) == 1 ) setTimeout(zen.omm, 500);
		
	});

	var zen = {}
		
	zen.$body = {}
	zen.$form = {}
	zen.$zen_container = {}
	zen.$zen = {}
	zen.$title = {}
	zen.$content = {}
	zen.$save_button = {}
	zen.$publish_button = {}
	zen.themes = []
	zen.elems = []
	zen.current_theme = {}
	zen.options = {}
	
	zen.init = function( ) {
		zen.options = zen_options;
		zen.themes = zen_themes;
		
		zen.$body = jQuery('body');
		zen.$form = jQuery('#post');
		zen.$zen_container = jQuery('<div/>')
			.attr('id', 'zen-container')
			.hide()
			.appendTo(zen.$form)
			;
		
		zen.resize();
		
		// Create the theme info div
		zen.current_theme = {};
		var $theme_info = jQuery('<div/>')
			.attr('id', 'zen-theme_info')
			.appendTo(zen.$zen_container);
		
		zen.current_theme.$name = jQuery('<span/>')
			.attr('id', 'zen-theme_name')
			.appendTo($theme_info)
			;
		zen.current_theme.$author = jQuery('<span/>')
			.attr('id', 'zen-theme_author')
			.appendTo($theme_info)
			;
		zen.current_theme.$credit = jQuery('<span/>')
			.attr('id', 'zen-theme_credit')
			.appendTo($theme_info)
			;
		
		jQuery('<a/>')
			.attr('id', 'zen-next_theme')
			.attr('href', '#')
			.html('Next Theme &raquo;')
			.bind('click', function(e) {
				e.preventDefault();
				zen.next_theme();
			})
			.appendTo(zen.$zen_container)
			;
		
		// Set the theme
		var theme_index = zen.get_theme_index(zen.options.active_theme);
		if( theme_index == -1 ) theme_index = 0;
		zen.set_theme(theme_index);
		
		$zen = jQuery('<div/>')
			.attr('id', 'zen')
			.appendTo(zen.$zen_container)
			;
			
		jQuery('<a/>')
			.addClass('zen-deactivate')
			.attr('href', '#unzen')
			.text('close')
			.prependTo(zen.$zen_container)
			;
		
		// Get all the DOM elements that get moved
		zen.$title = zen.get_elem('#titlediv', ['#edit-slug-box']);
		zen.$content = zen.get_elem('#content');
		zen.$save_button = zen.get_elem('#save-action');
		zen.$publish_button = zen.get_elem('#publishing-action');
		
		// Add elements that will be shown to the array 
		elems = [];
		elems.push(zen.$title)
		elems.push(zen.$content);
		elems.push(zen.$save_button);
		elems.push(zen.$publish_button);
		
		// on document resize, adjust the size of the div
		jQuery(window).bind('resize', zen.resize);
		
		zen.inactive_keys();
	}
	
	zen.omm = function( ) {
		
		// if tinymce active, switch
		tinymce_active = false;
		if (typeof(tinyMCE) != 'undefined' && tinyMCE.activeEditor != null && tinyMCE.activeEditor.isHidden() == false) {
			tinymce_active = true;
			switchEditors.go('content', 'html');
		}
		
		// go into zen mode
		zen.$zen_container.fadeIn('slow', function() {
			for( var i = 0; i < elems.length; i++ ) {
				zen.move( elems[i] );
			}
		});
		
		zen.$content.focus();
		
		// Load keyboard shortcuts
		zen.active_keys();
	}	
	
	zen.unomm = function( ) {
		// restores back to regular editing mode
		zen.$zen_container.fadeOut('slow', function(){
			
			for( var i = elems.length; i > 0; i--) {
				zen.restore(elems[i-1]);
			}
			
			if( tinymce_active ) {
				switchEditors.go('content', 'tinymce');
				$content.hide();
			}
		});
		
		// Load keyboard shortcuts
		zen.inactive_keys();
	}
	
	
	// grows or shirnks as necessary
	zen.resize = function( ) {
		zen.$zen_container.css({
			height: zen.$body.height(),
			width: zen.$body.width()
		});
	}
	
	
	zen.get_elem = function( selector, hide ) {
		var $obj = jQuery(selector)
					// track position of moved modules i.e. reference to prev, next and parent for each of element
					.data('parent', jQuery(selector).parent())
					.data('prev', jQuery(selector).prev())
					.data('next', jQuery(selector).next())
					;
		
		// Optional child elements that should be hidden during zen mode
		if( hide ) {
			for( var i = 0; i < hide.length; i++ ) {
				$obj.data('hide', $obj.find(hide[i]));
			}
		}
		
		return $obj;
	}
	
	
	zen.move = function( $obj ) {
		$obj.appendTo($zen);
		
		if(!$obj.is(':visible')) $obj.show();
		
		var $hide = $obj.data('hide');
		
		if( typeof $hide !== 'undefined' && $hide.length ) {
			$hide.each(function(i) {
				jQuery(this).hide();
			});
		}
		return $obj;
	}
	
	zen.restore = function( $obj ) {
		var $prev = $obj.data('prev');
		var $next = $obj.data('next');
		var $parent = $obj.data('parent');
		
		if( $prev.length )
			$prev.after($obj);
		else if ( $next.length )
			$next.before($obj);
		else if ( $parent.length )
			$parent.append($obj);
		
		var $hide = $obj.data('hide');
		
		if( typeof $hide !== 'undefined' && $hide.length ) {
			$obj.data('hide').each(function(i) {
				jQuery(this).show();
			});
		}
		return $obj;
	}
	
	zen.next_theme = function( ) {
		var current_index = zen.current_theme.index
		
		if( current_index == zen.themes.length - 1 )
			current_index = 0;
		else
			current_index = (current_index + 1);
		zen.set_theme(current_index)
	}
	
	zen.set_theme = function( index ) {
		
		var theme = zen.themes[index];
		
		// switch classes
		zen.$zen_container
			.removeClass()
			.addClass(theme.slug)
			;
		
		// update theme info
		zen.update_theme_info(theme);
		
		// set current_theme_index
		zen.current_theme.index = index;
		zen.current_theme.slug = theme.slug;
	}
	
	zen.update_theme_info = function( theme ) {
		zen.current_theme.$name.html(theme.name);
		zen.current_theme.$author.html(theme.author);
		zen.current_theme.$credit.html(theme.credit);
	}
	
	zen.get_theme_index = function( slug ) {
		for( var i = 0; i < zen.themes.length; i++ ) {
			if( zen.themes[i].slug == slug )
				return i;
		}
		return -1;
	}
	
	zen.inactive_keys = function( ) {
		jQuery(document).shortkeys({
			'z': zen.omm
		});
	}
	
	zen.active_keys = function( ) {
		jQuery(document).shortkeys({
			'escape': zen.unomm,
			'q': zen.unomm,
			't': zen.next_theme
		},{
			'escape': 27
		});
	}
	
})(jQuery);