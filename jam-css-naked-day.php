<?php
/*
Plugin Name: JAM CSS Naked Day
Plugin URI: http://joshmccarty.com/2012/04/a-css-naked-day-wordpress-plugin/
Description: CSS Naked Day takes place on April 9th. The idea is to promote web standards by removing all CSS from your website and leaving just semantic HTML. This plugin removes external style sheets, embedded style sheets, and style attributes from all pages on April 9th. It provides a few options as well to tailor the plugin to your preferences under the Appearance menu.
Version: 1.0
Author: Josh McCarty
Author URI: http://joshmccarty.com
License: GNU General Public License, version 2 (GPL).
*/

/*  Copyright 2012  Josh McCarty  (email: info@joshmccarty.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Based on CSS Naked Day plugin by ajalapus (http://wordpress.org/extend/plugins/css-naked-day-noscript/)

/**
 * Register the form setting for our jam_css_naked_day_options array.
 *
 * This function is attached to the admin_init action hook.
 *
 * This call to register_setting() registers a validation callback, jam_css_naked_day_options_validate(),
 * which is used when the option is saved, to ensure that our option values are complete, properly
 * formatted, and safe.
 *
 * We also use this function to add our plugin option if it doesn't already exist.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_options_init() {

	// Check that the current user can access the options page.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// If we have no options in the database, let's add them now.
	if ( false === jam_css_naked_day_get_options() )
		add_option( 'jam_css_naked_day_options', jam_css_naked_day_get_default_options() );

	register_setting(
		'jam_css_naked_day_options',         // Options group, see settings_fields() call in jam_css_naked_day_options_render_page()
		'jam_css_naked_day_options',         // Database option, see jam_css_naked_day_get_options()
		'jam_css_naked_day_options_validate' // The sanitization callback, see jam_css_naked_day_options_validate()
	);

	// Register our settings field group
	add_settings_section(
		'general',                  // Unique identifier for the settings section
		'',                         // Section title (we don't want one)
		'__return_false',           // Section callback (we don't want anything)
		'jam_css_naked_day_options' // Menu slug, used to uniquely identify the page; see jam_css_naked_day_options_add_page()
	);

	// Register our individual settings fields
	add_settings_field(
		'preview_checkbox',                                  // Unique identifier for the field for this section
		__( 'Preview Mode' ),                                // Setting field label
		'jam_css_naked_day_settings_field_preview_checkbox', // Function that renders the settings field
		'jam_css_naked_day_options',                         // Menu slug, used to uniquely identify the page; see jam_css_naked_day_options_add_page()
		'general'                                            // Settings section. Same as the first argument in the add_settings_section() above
	);
	add_settings_field(
		'timeframe_radio_buttons',
		__( 'Timeframe' ),
		'jam_css_naked_day_settings_field_timeframe_radio_buttons',
		'jam_css_naked_day_options',
		'general'
	);
	add_settings_field(
		'script_mode_radio_buttons',
		__( 'Script Mode' ),
		'jam_css_naked_day_settings_field_script_mode_radio_buttons',
		'jam_css_naked_day_options',
		'general'
	);
	add_settings_field(
		'message_radio_buttons',
		__( 'Display Message' ),
		'jam_css_naked_day_settings_field_message_radio_buttons',
		'jam_css_naked_day_options',
		'general'
	);
	add_settings_field(
		'message_textarea',
		__( 'Message Text' ),
		'jam_css_naked_day_settings_field_message_textarea',
		'jam_css_naked_day_options',
		'general'
	);
}
add_action( 'admin_init', 'jam_css_naked_day_options_init' );


/**
 * Add our theme options page to the admin menu.
 *
 * This function is attached to the admin_menu action hook.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_options_add_page() {
	$theme_page = add_submenu_page(
		'themes.php',
		__( 'CSS Naked Day Options' ),          // Name of page
		__( 'CSS Naked Day' ),                  // Label in menu
		'manage_options',                       // Capability required
		'jam_css_naked_day_options',            // Menu slug, used to uniquely identify the page
		'jam_css_naked_day_options_render_page' // Function that renders the options page
	);
}
add_action( 'admin_menu', 'jam_css_naked_day_options_add_page' );

/**
 * Returns an array of timeframe radio options registered for JAM CSS Naked Day.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_timeframe_radio_buttons() {
	$timeframe_radio_buttons = array(
		'local' => array(
			'value' => 'local',
			'label' => __( '24 hours in your website time zone' )
		),
		'global' => array(
			'value' => 'global',
			'label' => __( '48 hours (encompass all time zones)' )
		)
	);

	return apply_filters( 'jam_css_naked_day_timeframe_radio_buttons', $timeframe_radio_buttons );
}

/**
 * Returns an array of script mode radio options registered for JAM CSS Naked Day.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_script_mode_radio_buttons() {
	$script_mode_radio_buttons = array(
		'js' => array(
			'value' => 'js',
			'label' => __( 'Use JavaScript (may cause a flash of styled content when the page initially loads)' )
		),
		'php' => array(
			'value' => 'php',
			'label' => __( 'Use PHP output buffering (may cause slower load times for pages)' )
		)
	);

	return apply_filters( 'jam_css_naked_day_script_mode_radio_buttons', $script_mode_radio_buttons );
}

/**
 * Returns an array of sample radio options registered for JAM CSS Naked Day.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_message_radio_buttons() {
	$message_radio_buttons = array(
		'yes' => array(
			'value' => 'yes',
			'label' => __( 'Yes' )
		),
		'no' => array(
			'value' => 'no',
			'label' => __( 'No' )
		)
	);

	return apply_filters( 'jam_css_naked_day_sample_radio_buttons', $message_radio_buttons );
}

/**
 * Returns the default options for JAM CSS Naked Day.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_get_default_options() {
	$default_options = array(
		'preview_checkbox' => 'off',
		'timeframe_radio_buttons' => 'local',
		'script_mode_radio_buttons' => 'js',
		'message_radio_buttons' => 'yes',
		'message_textarea' => '<h3>What happened to the design?</h3><p>To know more about why styles are disabled on this website visit the <a href="http://naked.threepixeldrift.com" title="Web Standards Naked Day Host Website" target="_blank">Annual CSS Naked Day</a> website for more information.</p>',
	);

	return apply_filters( 'jam_css_naked_day_default_options', $default_options );
}

/**
 * Returns the options array for JAM CSS Naked Day.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_get_options() {
	return get_option( 'jam_css_naked_day_options', jam_css_naked_day_get_default_options() );
}

/**
 * Renders the preview checkbox setting field.
 */
function jam_css_naked_day_settings_field_preview_checkbox() {
	$options = jam_css_naked_day_get_options();
	?>
	<label for"preview-checkbox">
		<input type="checkbox" name="jam_css_naked_day_options[preview_checkbox]" id="preview-checkbox" <?php checked( 'on', $options['preview_checkbox'] ); ?> />
		<?php _e( 'Check this box to see how your site looks without CSS right now (only for logged in users who have access to manage options for the site).' );  ?>
	</label>
	<?php
}

/**
 * Renders the timeframe radio options setting field.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_settings_field_timeframe_radio_buttons() {
	$options = jam_css_naked_day_get_options();

	foreach ( jam_css_naked_day_timeframe_radio_buttons() as $button ) {
	?>
	<div class="layout">
		<label class="description">
			<input type="radio" name="jam_css_naked_day_options[timeframe_radio_buttons]" value="<?php echo esc_attr( $button['value'] ); ?>" <?php checked( $options['timeframe_radio_buttons'], $button['value'] ); ?> />
			<?php echo $button['label']; ?>
		</label>
	</div>
	<?php
	}
}

/**
 * Renders the script mode radio options setting field.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_settings_field_script_mode_radio_buttons() {
	$options = jam_css_naked_day_get_options();

	foreach ( jam_css_naked_day_script_mode_radio_buttons() as $button ) {
	?>
	<div class="layout">
		<label class="description">
			<input type="radio" name="jam_css_naked_day_options[script_mode_radio_buttons]" value="<?php echo esc_attr( $button['value'] ); ?>" <?php checked( $options['script_mode_radio_buttons'], $button['value'] ); ?> />
			<?php echo $button['label']; ?>
		</label>
	</div>
	<?php
	}
}

/**
 * Renders the message radio options setting field.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_settings_field_message_radio_buttons() {
	$options = jam_css_naked_day_get_options();

	foreach ( jam_css_naked_day_message_radio_buttons() as $button ) {
	?>
	<div class="layout">
		<label class="description">
			<input type="radio" name="jam_css_naked_day_options[message_radio_buttons]" value="<?php echo esc_attr( $button['value'] ); ?>" <?php checked( $options['message_radio_buttons'], $button['value'] ); ?> />
			<?php echo $button['label']; ?>
		</label>
	</div>
	<?php
	}
}

/**
 * Renders the message textarea setting field.
 */
function jam_css_naked_day_settings_field_message_textarea() {
	$options = jam_css_naked_day_get_options();
	?>
	<textarea class="large-text" type="text" name="jam_css_naked_day_options[message_textarea]" id="message-textarea" cols="50" rows="10" /><?php echo stripslashes( esc_textarea( $options['message_textarea'] ) ); ?></textarea>
	<label class="description" for="message-textarea"><?php _e( 'Enter the message you want visitors to see when the come to your site on CSS Naked Day' ); ?></label>
	<?php
}

/**
 * Returns the options array for JAM CSS Naked Day.
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_options_render_page() {
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>CSS Naked Day Options</h2>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php
				settings_fields( 'jam_css_naked_day_options' );
				do_settings_sections( 'jam_css_naked_day_options' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Sanitize and validate form input. Accepts an array, return a sanitized array.
 *
 * @see jam_css_naked_day_options_init()
 * @todo set up Reset Options action
 *
 * @since JAM CSS Naked Day 1.0
 */
function jam_css_naked_day_options_validate( $input ) {
	$output = $defaults = jam_css_naked_day_get_default_options();

	// The preview checkbox should either be on or off
	if ( ! isset( $input['preview_checkbox'] ) )
		$input['preview_checkbox'] = 'off';
	$output['preview_checkbox'] = ( $input['preview_checkbox'] == 'on' ? 'on' : 'off' );

	// The timeframe radio button value must be in our array of radio button values
	if ( isset( $input['timeframe_radio_buttons'] ) && array_key_exists( $input['timeframe_radio_buttons'], jam_css_naked_day_timeframe_radio_buttons() ) )
		$output['timeframe_radio_buttons'] = $input['timeframe_radio_buttons'];

	// The script mode radio button value must be in our array of radio button values
	if ( isset( $input['script_mode_radio_buttons'] ) && array_key_exists( $input['script_mode_radio_buttons'], jam_css_naked_day_script_mode_radio_buttons() ) )
		$output['script_mode_radio_buttons'] = $input['script_mode_radio_buttons'];

	// The message radio button value must be in our array of radio button values
	if ( isset( $input['message_radio_buttons'] ) && array_key_exists( $input['message_radio_buttons'], jam_css_naked_day_message_radio_buttons() ) )
		$output['message_radio_buttons'] = $input['message_radio_buttons'];

	// The message textarea must be safe text with the allowed tags for posts
	if ( isset( $input['message_textarea'] ) )
		$output['message_textarea'] = wp_filter_post_kses( $input['message_textarea'] );

	return apply_filters( 'jam_css_naked_day_options_validate', $output, $input, $defaults );
}


if ( ! function_exists( 'jam_is_css_naked_day' ) ) :
function jam_is_css_naked_day() {

	$options = get_option( 'jam_css_naked_day_options' );
	if ( $options['timeframe_radio_buttons'] == 'global' ) {
		$css_naked_start_time = gmmktime( -12, 0, 0, 4, 9 );  // Start of global April 9
		$css_naked_end_time = gmmktime( 36, 0, 0, 4, 9 );   // End of global April 9
	}
	else {
		$css_naked_offset = get_option( 'gmt_offset' ) * 3600;
		$css_naked_start_time = gmmktime( 0, 0, 0, 4, 9 ) - $css_naked_offset;  // Start of local April 9
		$css_naked_end_time = gmmktime( 24, 0, 0, 4, 9 ) - $css_naked_offset;   // End of local April 9
	}

	$css_naked_now_time = time(); // Time now

	if ( ( $css_naked_now_time >= $css_naked_start_time && $css_naked_now_time <= $css_naked_end_time ) || ( $options['preview_checkbox'] == 'on' && current_user_can( 'manage_options' ) ) )
		return true;
	else
		return false;
}
endif;

if ( ! function_exists( 'jam_css_naked_day_replace' ) ) :
function jam_css_naked_day_replace( $css_naked_buffer ) {
	$css_naked_pattern = array(
		'@<\?xml-stylesheet.*\?>@sU',                               // x(ht)ml reference
		'@<link[^<]+rel\s*=\s*"[a-z ]*stylesheet[a-z ]*".*>@isU',   // (x)html reference "
		'@<link[^<]+rel\s*=\s*\'[a-z ]*stylesheet[a-z ]*\'.*>@isU', // (x)html reference '
		'@<style.*</style>@isU',                                    // (x)html embedded
		'@style\s*=\s*".*(?:(?:\x5C").*)*?"@iU',                    // (x)html inline "
		'@style\s*=\s*\'.*(?:(?:\x5C\').*)*?\'@iU'                  // (x)html inline '
	);
	return preg_replace( $css_naked_pattern, '', $css_naked_buffer );
}
endif;

if ( ! function_exists( 'jam_css_naked_day_buffer' ) ) :
function jam_css_naked_day_buffer() {
	if ( jam_is_css_naked_day() ) {
		$options = get_option( 'jam_css_naked_day_options' );
		add_action( 'wp_enqueue_scripts', 'jam_css_naked_day_script' );
		if ( $options['script_mode_radio_buttons'] == 'php' ) {
			ob_start( 'jam_css_naked_day_replace' );
		}
	}
}
endif;
add_action( 'template_redirect', 'jam_css_naked_day_buffer' );


function jam_css_naked_day_script() {
	$options = get_option( 'jam_css_naked_day_options' );
	wp_register_script( 'jam_css_naked_day', plugins_url( 'jam-css-naked-day.js', __FILE__ ), array( 'jquery' ), false, false );
	wp_enqueue_script( 'jam_css_naked_day' );
	wp_localize_script( 'jam_css_naked_day', 'jamCssNakedDayOptions', array( 'message' => stripslashes( $options['message_textarea'] ), 'scriptMode' => $options['script_mode_radio_buttons'], 'displayMessage' => $options['message_radio_buttons'] ) );
}
?>