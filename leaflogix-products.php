<?php
/**
 * @package Leaflogix Products
 * @version 0.0.0
 */
/*
Plugin Name: Leaflogix Products
Description: WordPress plugin to create products listing via the Leaflogix API.
Version: 0.0.0
Author: Leroy Rosales
Author URI: https://leroyrosales.com/
*/

add_action( "admin_menu", "leaflogix_admin_menu" );

function leaflogix_admin_menu() {
  add_submenu_page( 
    "options-general.php", 
    "Leaflogix API Settings", 
    "Leaflogix API", 
    "manage_options", 
    "leaflogix-settings", 
    "leaflogix_admin_func"
  );
}

function leaflogix_admin_func(){
  echo "<div class='wrap'><div id='dashicons-admin-network' class='icon32'><br></div>
  <h2>Leaflogix API Settings</h2></div>";
}
