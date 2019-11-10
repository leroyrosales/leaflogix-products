# Leaflogix Products

WordPress plugin to create products listing via the LeafLogix API.

## API Key

For an API key contact [LeafLogix](https://leaflogix.com/). For documentation and testing you can test your API key via the [LeafLogixAPI documentation](http://leaflogix-publicapi.azurewebsites.net/swagger/).

## Endpoints

This plugin is hard-coded to only ping the '/products' and '/inventory' endpoints. To add endpoints, simply find the endpoints from the above LeafLogix documentation and add to the URLs pinged at line 105 in the `leaflogix-products.php` file. 

## Shortcode

All endpoint data is returned to a shortcode list that can be printed on the frontend with `[leaflogix_products]`.
