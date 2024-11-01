<?php
/*
Plugin Name: Zen
Plugin URI: http://digitalize.ca
Description: A distraction-free environment for blogging; inspired by Habari, OmmWriter, WriteRoom, and countless wasted hours of staring at blank screens.
Author: Mohammad Jangda
Version: 1.2
Author URI: http://digitalize.ca/

Copyright 2010 Mohammad Jangda

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

define( 'ZEN_VERSION', 1.2 );
define( 'ZEN_URL', path_join( WP_PLUGIN_URL, basename( dirname( __FILE__ ) ) ) );
define( 'ZEN_PATH', dirname( __FILE__ ) );

class zen {

	var $options_key = 'zen';
	var $themes = array(
		array(
			'slug' => 'zen-light',
			'name' => 'Zen Light',
			'thumb' => '',
			'author' => '',
			'credit' => '',
		),
		array(
			'slug' => 'zen-dark',
			'name' => 'Zen Dark',
			'thumb' => '',
			'author' => '',
			'credit' => '',
		),
		array(
			'slug' => 'zen-papyrus',
			'name' => 'Zen Papyrus',
			'thumb' => '',
			'author' => '',
			'credit' => 'Background by <a href="http://www.flickr.com/photos/94857613@N00/167285657" target="_blank">DevonTT (flickr)</a>',
		),	
		array(
			'slug' => 'zen-papyrus-lite',
			'name' => 'Zen Papyrus Lite',
			'thumb' => '',
			'author' => ' by <a href="http://andrewspittle.net" target="_blank">Andrew Spittle</a>',
			'credit' => '',
		),	
		array(
			'slug' => 'zen-terminal',
			'name' => 'Zen Terminal',
			'thumb' => '',
			'author' => ' by <a href="http://andrewspittle.net" target="_blank">Andrew Spittle</a>',
			'credit' => '',
		),
		array(
			'slug' => 'zen-sunset',
			'name' => 'Zen Sunset',
			'thumb' => '',
			'author' => '',
			'credit' => 'Background by <a href="http://www.flickr.com/photos/elkit/2139523783/" target="_blank">elkit (flickr)</a>',
		),
		
		array(
			'slug' => 'zen-sky',
			'name' => 'Zen Sea &amp; Sky',
			'thumb' => '',
			'author' => '',
			'credit' => 'Background by <a href="http://www.sxc.hu/photo/311970" target="_blank">stevekrh19 (sxc.hu)</a>',
		),
		
		array(
			'slug' => 'zen-foiled',
			'name' => 'Zen Foiled Again',
			'thumb' => '',
			'author' => '',
			'credit' => 'Background by <a href="http://www.sxc.hu/photo/475858" target="_blank">asolario (sxc.hu)</a>',
		)
		
	);

	function __construct() {
		add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts'));
		add_action('admin_print_styles', array(&$this, 'admin_styles'));
		
		add_action('personal_options_update', array(&$this, 'update_user_profile'));
		add_action('profile_personal_options', array(&$this, 'show_user_profile_options'));
		
		add_action('save_post', array(&$this, 'update_active_theme'));
	}
	
	function show_user_profile_options( ) {
		global $user_id, $profileuser;
		
		if( current_user_can('edit_posts') ) :
			
			$user_options = $this->get_user_options($user_id);
			$themes = $this->get_themes();
			?>
			<h3><?php _e('Zen Settings', 'zen') ?></h3>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<?php _e('Zen Mode', 'zen') ?>
					</th>
					<td>
						<fieldset>
							<?php $this->_the_user_option_checkbox( 'onload', $user_options['onload'], __('Always open in Zen mode', 'zen'), $this->options_key); ?>
							<br />
							<em><?php _e('Enabling this will toggle Zen mode whenever you add or edit a post or page.', 'zen') ?></em>
							<br />
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e('Preferred theme', 'zen'); ?>
					</th>
					<td>
						<div id="zen-themes">
							<?php foreach( $themes as $theme ) : ?>
								<?php $selected = ( $theme['slug'] == $user_options['active_theme'] ) ? true : false; ?>
								<?php $this->_the_user_option_theme($theme['slug'], $theme, $selected) ?>
							<?php endforeach; ?>
						</div>
						<p><?php _e("Got any ideas for themes you'd like to see? You should <a href=\"mailto:batmoo@gmail.com?subject=Zen%20Themes\">email me</a>.", 'zen'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e('Keyboard Shortcuts', 'zen') ?>
					</th>
					<td class="zen-keyboard_shortcuts">
						<span><?php _e('Some handy shortcuts to help you go both distraction-free and mouse/touchpad/trackball-free as well.', 'zen') ?></span>
						<p>
							<span class="zen-keyboard_shortcut">z</span>
							<?php _e('Enter Zen Mode') ?>
						</p>
						<p>
							<span class="zen-keyboard_shortcut">q</span>
							<?php _e('Leave Zen Mode') ?>
						</p>
						<p>
							<span class="zen-keyboard_shortcut">t</span>
							<?php _e('Switch to next theme') ?>
						</p>
					</td>
				</tr>
			</table>
			
			<input type="hidden" name="zen_options_update" value="1" />
		<?php
		endif;
	}
	
	function _the_user_option_checkbox( $slug, $value, $label, $parent = '' ) {
		$slug = esc_attr($slug);
		
		if( $parent ) $name = $parent . '[' . $slug . ']';
		else $name = $slug;
		
		$checked = ( $value ) ? ' checked="checked"' : '';
		?>
		<label for="<?php echo $slug ?>">
			<input type="checkbox" id="<?php echo $slug ?>" value="1" name="<?php echo $name ?>"<?php echo $checked ?> />
			<?php echo $label ?>
		</label>
		<?php
	}
	
	function _the_user_option_theme( $slug, $theme, $selected ) {
		$name = $this->options_key . '[active_theme]';
		$checked = ( $selected ) ? ' checked="checked"' : '';
		?>
		<div class="zen-theme">
			<div class="zen-theme_details">
				<label for="<?php echo $slug ?>">
					<img src="<?php echo $this->get_theme_thumbnail( $theme ); ?>" alt="<?php echo sprintf( __('Thumbnail for Zen Theme: %s', 'zen'), $theme['name']) ?>" class="zen-theme_thumbnail" />
					<input type="radio" id="<?php echo $slug ?>" name="<?php echo $name ?>" value="<?php echo $slug ?>" <?php echo $checked ?> />
					<span class="zen-theme_name"><?php echo $theme['name'] ?></span>
					<span class="zen-theme_author"><?php echo $theme['author'] ?></span>
				</label>
			</div>
		</div>
		<?php
	}	
	
	function update_user_profile(  ) {
		
		$user = wp_get_current_user();
		if ( !$user || $user->ID == 0 )
			return;
		$user_id = $user->ID;
		
		if( current_user_can('edit_posts') && isset($_POST['zen_options_update']) ) {
			$options = $_POST[$this->options_key];
			
			// Scrub the submitted options
			if( isset( $options['onload'] ) ) 
				$options['onload'] = intval( $options['onload'] );
			if( isset( $options['active_theme'] ) )
				$options['active_theme'] = sanitize_key( $options['active_theme'] );			
			
			$this->update_user_options( $user_id, $this->options_key, $options );
		}
	}
	
	function update_user_options( $user_id = 0, $option_key, $option_value ) {
		
		if( !$user_id ) {
			$user = wp_get_current_user();
			$user_id = $user->ID;
		}
		if( !$user_id ) return false;
		
		update_metadata('user', $user_id, $option_key, $option_value );
	}
	
	function get_user_options( $user_id = 0 ) {
		
		if( !$user_id ) {
			$user = wp_get_current_user();
			$user_id = $user->ID;
		}
		
		if( $user_id )
			$options = get_metadata( 'user', $user_id, $this->options_key, true );
		
		if( empty($options) ) {
			$options = array();
		}
		
		// Set option defaults, if not assigned
		if( empty($options['onload']) ) $options['onload'] = 0;
		if( empty($options['active_theme']) ) $options['active_theme'] = 'zen-light';
		
		return $options;
	}
	
	/**
	 * Updates the user's current theme as their preferred theme
	 */
	function update_active_theme( $post ) {
		if( wp_is_post_autosave($post) && isset( $_POST['active_theme'] ) ) {
			$active_theme = esc_html( trim( $_POST['active_theme'] ) );
			$options = $this->get_user_options();
			if( $active_theme && !empty($options) && $active != $options['active_theme'] ) {
				$options['active_theme'] = $active_theme;
				$this->update_user_options( 0, $this->options_key, $options );
			}
		}
	}
	
	function get_themes( ) {
		return $this->themes;
	}
	
	function get_option( $slug ) {
		return $this->options[$this->get_option_name($slug)];
	}
	
	function get_option_name( $slug ) {
		return $this->options_key . '_' . $slug;
	}

	function admin_scripts( ) {
		
		if( $this->is_edit_page() ) {
			wp_enqueue_script('jquery-shortkeys', ZEN_URL . '/js/jquery.shortkeys.js', array('jquery'), ZEN_VERSION, true);
			wp_enqueue_script('zen-js', ZEN_URL . '/js/zen.js', array('jquery', 'jquery-shortkeys'), ZEN_VERSION, true);
			
			$zen_options = $this->get_user_options();
			
			$themes = $this->get_themes();
			if( function_exists('json_encode') ) {
				$themes = json_encode($themes);
			} else {
				require_once(ABSPATH."/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");
				$json_obj = new Moxiecode_JSON();
				$themes = $json_obj->encode($themes);
			}
			$zen_options['themes'] = $themes;
			
			wp_localize_script('zen-js', 'zen_options', $zen_options);
			?>
			<script type="text/javascript">
				var zen_themes = <?php echo $themes ?>;
			</script>
			<?php
		}		
	}
	
	function admin_styles( ) {
		if( $this->is_edit_page() ) {
			wp_enqueue_style('zen-css', ZEN_URL . '/css/zen.css', false, false, 'all');
		}
		if( $this->is_options_page() ) {
			wp_enqueue_style('zen-admin-css', ZEN_URL . '/css/zen-admin.css', false, false, 'all');
		}
	}
	
	function is_edit_page( ) {
		global $pagenow;
		
		$edit_pages = array('post-new.php', 'post.php', 'page.php', 'page-new.php');
		
		if( in_array($pagenow, $edit_pages) )
			return true;
		else
			return false;
	}
	
	function is_options_page( ) {
		global $pagenow, $plugin_page;
		
		if( is_admin() && $pagenow == 'profile.php' )
			return true;
			
		return false;
	}
	
	function get_theme_thumbnail( $theme ) {
		return sprintf( '%s/images/themes/%s.jpg', ZEN_URL, $theme['slug'] );
	}
}

add_action('init', 'zen_init');

function zen_init( ) {
	if( is_admin() ) {
		global $zen;
		$zen = new zen();
	}
}

?>