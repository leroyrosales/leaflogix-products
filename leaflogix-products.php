<?php
/**
 * @package Leaflogix Products
 * @version 0.1.0
 */
/*
Plugin Name: Leaflogix Products
Description: WordPress plugin to create products listing via the Leaflogix API.
Version: 0.1.0
Author: Leroy Rosales
Author URI: https://leroyrosales.com/
*/

define( "LEAFLOGIX_SETTINGS_PAGE_SLUG", "leaflogix-settings" );

function leaflogix_settings_page() {

	add_submenu_page(
		"options-general.php", // top level menu page
		"Leaflogix API Settings", // title of the settings page
		"Leaflogix Settings", // title of the submenu
		"manage_options", // capability of the user to see this page
		LEAFLOGIX_SETTINGS_PAGE_SLUG, // slug of the settings page
		"leaflogix_settings_page_func" // callback function to be called when rendering the page
  );
  
  add_action( "admin_init", "leaflogix_settings_init" );
  
}
add_action( "admin_menu", "leaflogix_settings_page" );

function leaflogix_settings_init() {

	add_settings_section(
		"leaflogix-settings-section", // id of the section
		"Leaflogix Settings", // title to be displayed
		"", // callback function to be called when opening section
		LEAFLOGIX_SETTINGS_PAGE_SLUG // page on which to display the section, this should be the same as the slug used in add_submenu_page()
	);
	// register the setting
	register_setting(
		LEAFLOGIX_SETTINGS_PAGE_SLUG, // option group
		"leaflogix_swagger_api_key"
  );
  
	add_settings_field(
		"leaflogix-api-key", // id of the settings field
		"API Key", // title
		"leaflogix_settings_cb", // callback function
		LEAFLOGIX_SETTINGS_PAGE_SLUG, // page on which settings display
		"leaflogix-settings-section" // section on which to show settings
  );
  
}

// Leaflogix API callback
function leaflogix_settings_cb() {

	$api_key_text = esc_attr(get_option("leaflogix_swagger_api_key", ""));
	?>
    <div id="titlediv">
        <input id="title" type="text" name="leaflogix_swagger_api_key" value="<?php echo $api_key_text; ?>">
    </div>
    <?php

}

// The settings page form
function leaflogix_settings_page_func() {
	// check user capabilities
	if (!current_user_can("manage_options")) {
		return;
	}
	?>

    <div class="wrap">
        <?php settings_errors();?>
        <form method="POST" action="options.php">
		    <?php settings_fields( LEAFLOGIX_SETTINGS_PAGE_SLUG );?>
		    <?php do_settings_sections( LEAFLOGIX_SETTINGS_PAGE_SLUG )?>
		    <?php submit_button();?>
        </form>
    </div>
    <?php

}
