<?php
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
		"options-general.php",
		"Leaflogix API Settings",
		"Leaflogix Settings",
		"manage_options",
		LEAFLOGIX_SETTINGS_PAGE_SLUG,
		"leaflogix_settings_page_func"
  );
  
  add_action( "admin_init", "leaflogix_settings_init" );
  
}
add_action( "admin_menu", "leaflogix_settings_page" );

function leaflogix_settings_init() {

	add_settings_section(
		"leaflogix-settings-section",
		"Leaflogix Settings",
		"",
		LEAFLOGIX_SETTINGS_PAGE_SLUG
	);

	// register the settings
	register_setting(
		LEAFLOGIX_SETTINGS_PAGE_SLUG,
		"leaflogix_swagger_api_key"
  );
  
	add_settings_field(
		"leaflogix-api-key",
		"API Key",
		"leaflogix_settings_cb",
		LEAFLOGIX_SETTINGS_PAGE_SLUG,
		"leaflogix-settings-section"
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
		
		<p><em>For an API key contact <a href="https://leaflogix.com/" target="_blank">Leaflogix</a>. For documentation and testing you can test your API key via the <a href="http://leaflogix-publicapi.azurewebsites.net/swagger/" target="_blank">LeafLogixAPI documentation</a>.</em></p>

  </div> 
	<?php

}

// GET response function
function http_get_products_response() {

	if ( false === ( $products = get_transient( 'cached_products' ) ) ) {

		$api_key_text = base64_encode( esc_attr(get_option("leaflogix_swagger_api_key", "")) );

		$args = array(
			'method'      => 'GET',
			'headers'     => array(
				"Content-Type" => "application/json; charset=utf-8",
				"Authorization" => "Basic " . $api_key_text
			),
		);

		// api endpoints
		$products_api = esc_url_raw( "http://leaflogix-publicapi.azurewebsites.net/products" );
		$inventroy_api = esc_url_raw( "http://leaflogix-publicapi.azurewebsites.net/inventory?includeLabResults=true&includeRoomQuantities=true" );

		$products_response = wp_safe_remote_get( $products_api, $args );
		$inventory_response = wp_safe_remote_get( $inventroy_api, $args );

		// response data
		$products_body = wp_remote_retrieve_body( $products_response );
		$inventory_body = wp_remote_retrieve_body( $inventory_response );

		// decode data to arrays
		$products_json = json_decode( $products_body, true );
		$inventory_json = json_decode( $inventory_body, true );

		// merge arrays
		$products = array_column(array_merge($products_json,$inventory_json), NULL, 'sku');

		set_transient( 'cached_products', $products, 12 * 60 * 60 ); // 12 hours

		$products = get_transient( 'cached_products' );

	}

	// foreach loop
	foreach($products as $product){
		echo "<ul>";
			echo "<li>SKU: <em>" . $product["sku"] . "</em></li>";
			echo "<li>Name: <strong>" . $product["productName"] . "</strong></li>";
			if( $product["description"] ) {
				echo "<li>Description: " . $product["description"] . "</li>";
			}
			echo "<li>Category: " . $product["category"] . "</li>";
			if($product["imageUrl"]){
				echo "<li><img alt='Photo of " . $product["productName"] ."' src='" . $product["imageUrl"] . "'></li>";
			}
			if($product["quantityAvailable"]) {
				echo "<li>Quantity: " . $product["quantityAvailable"] . "</li>";
			}
			if($product["netWeight"]){
				echo "<li>Net weight:" . $product["netWeight"] . "</li>";
			}
			if($product["strain"]){
				echo "<li>Strain: " . $product["strain"] . "</li>";
			}
			if($product["size"]){
				echo "<li>Size: " . $product["size"] . "</li>";
			}
			if($product["vendorName"]) {
				echo "<li>Vendor: " . $product["vendorName"] . "</li>";
			}
			if($product["thcContent"]){
				echo "<li>THC content:" . $product["thcContent"] . "</li>";
			}
			if($product["thcContentUnit"]){
				echo "<li>THC content unit: " . $product["thcContentUnit"] . "</li>";
			}
			if($product["cbdContent"]){
				echo "<li>CBD content: " . $product["cbdContent"] . "</li>";
			}
			if($product["cbdContentUnit"]){
				echo "<li>CBD content unit: " . $product["cbdContentUnit"] . "</li>";
			}
			if($product["brandName"]){
				echo "<li>Brand name: " . $product["brandName"] . "</li>";
			}
		echo "</ul>";
	}

}
add_shortcode('leaflogix_products', 'http_get_products_response');

