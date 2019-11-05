<?php
/**
 * @package Leaflogix Products
 * @version 0.2.0
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
		<style>
		pre {
			width: 95%; overflow: auto; margin: 20px 0; padding: 20px;
			color: #fff; background-color: #424242;
		}
	</style>
        <?php settings_errors();?>
        <form method="POST" action="options.php">
		    <?php settings_fields( LEAFLOGIX_SETTINGS_PAGE_SLUG );?>
		    <?php do_settings_sections( LEAFLOGIX_SETTINGS_PAGE_SLUG )?>
		    <?php submit_button();?>
        </form>
        <div class="help-text"><em>For an API key contact <a href="https://leaflogix.com/" target="_blank">Leaflogix</a>. For documentation and testing you can test your API key via the <a href="http://leaflogix-publicapi.azurewebsites.net/swagger/" target="_blank">LeafLogixAPI documentation</a>.</em></div>

				<?php echo http_get_products_response(); ?>
    </div>
    <?php

}

// GET response function
function http_get_products_response() {

	$api_key_text = base64_encode( esc_attr(get_option("leaflogix_swagger_api_key", "")) );

	$args = array(
		'method'      => 'GET',
		'headers'     => array(
			"Content-Type" => "application/json; charset=utf-8",
			// "Cache-Control" => "max-age=3600",
			"Authorization" => "Basic " . $api_key_text
		),
	);

	$url = esc_url_raw( 'http://leaflogix-publicapi.azurewebsites.net/products' );

	$response = wp_safe_remote_get( $url, $args );

	// response data
	$code    = wp_remote_retrieve_response_code( $response );
	$message = wp_remote_retrieve_response_message( $response );
	$body    = wp_remote_retrieve_body( $response );
	$headers = wp_remote_retrieve_headers( $response );

	$header_date  = wp_remote_retrieve_header( $response, 'date' );
	$header_type  = wp_remote_retrieve_header( $response, 'content-type' );
	$header_cache = wp_remote_retrieve_header( $response, 'cache-control' );


	// output data
	//$output  = '<h2><code>'. $url .'</code></h2>';

	//$output .= 'API KEY: <code>' . $api_key_text . '</code>';

	//$output .= '<h3>Status</h3>';
	//$output .= '<div>Response Code: '    . $code    .'</div>';
	//$output .= '<div>Response Message: ' . $message .'</div>';


	$products = json_decode( $body );

	//set_transient( 'api_info', $products, 12 * 60 * 60 );

	foreach ($products as $product) {
		echo "<ul>";
			echo "<li>Sku: <em>" . $product->sku . "</em></li>";
			echo "<li>Name: <strong>" . $product->productName . "</strong></li>";
			echo "<li>Description: " . $product->description . "</li>";
			echo "<li>Category: " . $product->category . "</li>";
			if($product->image){
				echo "<li>Image: " . $product->image . "</li>";
			}
			if($product->netWeight){
				echo "<li>Net weight:" . $product->netWeight . "</li>";
			}
			if($product->strain){
				echo "<li>Strain: " . $product->strain . "</li>";
			}
			if($product->size){
				echo "<li>Size: " . $product->size . "</li>";
			}
			echo "<li>Vendor: " . $product->vendorName . "</li>";
			if($product->thcContent){
				echo "<li>THC content:" . $product->thcContent . "</li>";
			}
			if($product->thcContentUnit){
				echo "<li>THC content unit: " . $product->thcContentUnit . "</li>";
			}
			if($product->cbdContent){
				echo "<li>CBD content: " . $product->cbdContent . "</li>";
			}
			if($product->cbdContentUnit){
				echo "<li>CBD content unit: " . $product->cbdContentUnit . "</li>";
			}
			if($product->brandName){
				echo "<li>Brand name: " . $product->brandName . "</li>";
			}
		echo "</ul>";
		// sku
		// product name
		// description
		// category
		// image
		// quantity available
		// weight, including flower equivalent
		// batch name
		// package status
		// price
		// strain
		// size
		// tested date
		// sample date
		// packaged date
		// labResults
		// labResultUnit
		// vendorName
		// thcContent
		// thcContentUnit
		// cbdContent
		// cbdContentUnit
		// brandName 
	}

	// $output .= '<h3>Headers</h3>';
	// $output .= '<div>Response Date: ' . $header_date  .'</div>';
	// $output .= '<div>Content Type: '  . $header_type  .'</div>';
	// $output .= '<div>Cache Control: ' . $header_cache .'</div>';
	// $output .= '<pre>';
	// ob_start();
	// var_dump( $headers );
	// $output .= ob_get_clean();
	// $output .= '</pre>';

	// return $output;

}
add_shortcode('leaflogix_products', 'http_get_products_response');
