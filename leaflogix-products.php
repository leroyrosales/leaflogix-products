<?php
/*
Plugin Name: LeafLogix Products
Description: WordPress plugin to create products listing via the LeafLogix API.
Version: 1.0
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
	
	ob_start();

	// foreach loop
	foreach($products as $product){ ?>
		<ul>
			<li>Name: <strong><?php echo $product["productName"]; ?></strong></li>
			<li>SKU: <em><?php echo $product["sku"]; ?></em></li>
			<?php if( $product["description"] ) { ?>
				<li>Description: <?php echo $product["description"]; ?></li>
			<?php } ?>
			<li>Category: <?php echo $product["category"]; ?></li>
			<?php if( $product["imageUrl"] ) { ?>
				<li><img alt="Photo of "<?php echo $product["productName"]; ?>" src="<?php echo $product["imageUrl"]; ?>"></li>
			<?php } ?>
			<?php if( $product["quantityAvailable"] ) { ?>
				<li>Quantity: <?php echo $product["quantityAvailable"]; ?></li>
			<?php } ?>
			<?php if( $product["netWeight"] ) { ?>
				<li>Net weight: <?php echo $product["netWeight"]; ?></li>
			<?php } ?>
			<?php if( $product["strain"] ) { ?>
				<li>Strain: <?php echo $product["strain"]; ?></li>
			<?php } ?>
			<?php if( $product["size"] ) { ?>
				<li>Size: <?php echo $product["size"]; ?></li>
			<?php } ?>
			<?php if( $product["vendorName"] ) { ?>
				<li>Vendor: <?php echo $product["vendorName"]; ?></li>
			<?php } ?>
			<?php if( $product["thcContent"] ) { ?>
				<li>THC content: <?php echo $product["thcContent"]; ?></li>
			<?php } ?>
			<?php if( $product["thcContentUnit"] ) { ?>
				<li>THC content unit: <?php echo $product["thcContentUnit"]; ?></li>
			<?php } ?>
			<?php if( $product["cbdContent"] ) { ?>
				<li>CBD content: <?php echo $product["cbdContent"]; ?></li>
			<?php } ?>
			<?php if( $product["cbdContentUnit"] ) { ?>
				<li>CBD content unit: <?php echo $product["cbdContentUnit"]; ?></li>
			<?php } ?>
			<?php if( $product["brandName"] ) { ?>
				<li>Brand name: <?php echo $product["brandName"]; ?></li>
			<?php } ?>
		</ul>
	<?php 
	}

	$leaflogix_products = ob_get_clean();

	return $leaflogix_products;

}
add_shortcode('leaflogix_products', 'http_get_products_response');

