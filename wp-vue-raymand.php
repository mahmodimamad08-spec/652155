<?php
/**
 * Plugin Name: Raymand Shop
 * Description: .افزونه رایمند برای سهولت انتقال اطلاعات از سرور رایمند به فروشگاه اینترنتی تحت افزونه ووکامرس تهییه شده است  
 */

 const PLUGIN_SLUG_NAME = 'vue-admin-setting-panel';
 define( 'RAYMAND__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
//  URl
 function my_vue_panel_page() {
     ?>
     <link href="https://edcshop.ir//wp-content/plugins/wp-vue-raymand/app/public/assets/css/custom.css" rel="stylesheet"></link>
     <div id="app" class="pa-1">
     </div>
     <?php
    
    // wp_enqueue_script( PLUGIN_SLUG_NAME, plugin_dir_url( __FILE__ ). 'app/dist/assets/index-767464ae.js', array(), time(), true );
    wp_enqueue_script( PLUGIN_SLUG_NAME, plugin_dir_url( __FILE__ ). 'app/dist/assets/index-35f3b913.js', array(), time(), true );
    // wp_enqueue_style( PLUGIN_SLUG_NAME, plugin_dir_url( __FILE__ ). 'app/dist/assets/index-ddd52e85.css', array(), time());
    wp_enqueue_style( PLUGIN_SLUG_NAME, plugin_dir_url( __FILE__ ). 'app/dist/assets/index-ddd52e85.css', array(), time());
 }
 function add_menu_item() {
     add_menu_page("Vue.js Admin Panel", "افزونه رایمند", "manage_options",
         PLUGIN_SLUG_NAME, "my_vue_panel_page", 'dashicons-screenoptions', 99999);
 }
 add_action("admin_menu", "add_menu_item");

add_filter("script_loader_tag", "add_module_to_my_script", 10, 3);
function add_module_to_my_script($tag, $handle, $src)
{
    if (PLUGIN_SLUG_NAME === $handle) {
        $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }

    return $tag;
}

function allow_unsafe_urls ( $args ) {
    $args['reject_unsafe_urls'] = false;
    return $args;
 } ;

add_filter( 'http_request_args', 'allow_unsafe_urls' );

 require_once( RAYMAND__PLUGIN_DIR . 'class.raymand-db.php' );
 require_once( RAYMAND__PLUGIN_DIR . 'web_service/class.raymand-rest-api.php' );

 add_action( 'rest_api_init', array( 'Raymand_REST_API', 'init' ) );
 
 add_action( 'woocommerce_rest_insert_product', 'handle_custom_product_meta_fields_from_api', 10, 3 );

function handle_custom_product_meta_fields_from_api( $product, $request, $creating ) {
    if ( isset( $request['minimum_order_quantity'] ) ) {
        update_post_meta( $product->get_id(), '_minimum_order_quantity', sanitize_text_field( $request['minimum_order_quantity'] ) );
    }

    if ( isset( $request['maximum_order_quantity'] ) ) {
        update_post_meta( $product->get_id(), '_maximum_order_quantity', sanitize_text_field( $request['maximum_order_quantity'] ) );
    }

    if ( isset( $request['step_order_quantity'] ) ) {
        update_post_meta( $product->get_id(), '_step_order_quantity', sanitize_text_field( $request['step_order_quantity'] ) );
    }
    
    if ( isset( $request['unit_price'] ) ) {
        update_post_meta( $product->get_id(), '_unit_price', sanitize_text_field( $request['unit_price'] ) );
    }
}

