<?php
/**
 * @package Intrackt-Companion-Products
 */
/*
Plugin Name: Intrackt Companion Products
Plugin URI: https://intrackt.com/plugins-companion-products/
Description: Support adding required Companion Products to the cart whenever the main product is added to the cart 
Version: 1.0.2
Author: Intrackt
Author URI: https://Intrackt.com
License: GPLv2 or later
Text Domain: Intrackt
*/

/*
Warranty and license

Copyright 2017 Intrackt
*/

/*
 * Define useful constants
 */
define('INTRACKT_PLUGIN_NAME_COMPANIONPRODUCTS','CompanionProducts');
define('INTRACKT_COMPANIONPRODUCTS_VERSION','1.0.2');
define('INTRACKT_COMPANIONPRODUCTS_MINIMUM_WP_VERSION','4.8.1');
define('INTRACKT_COMPANIONPRODUCTS_PLUGIN_DIR',plugin_dir_path( __FILE__));
define('INTRACKT_COMPANIONPRODUCTS_TESTMODE',false);

/*
 * common for all plugins
 */
{
    /*
     * Common test to bail out if not executed from with WP
     */
    if ( !function_exists( 'add_action' ) ) {
        echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
        exit;
    }

    /*
     * load the source for any required classes or files all plugins
     */
    require_once( INTRACKT_COMPANIONPRODUCTS_PLUGIN_DIR . 'class.Intrackt-Companion-Products-Manager.php' );
    require_once( INTRACKT_COMPANIONPRODUCTS_PLUGIN_DIR . 'class.Intrackt-Companion-Products-Common.php' );

    /*
     * add links to the plugin on the plugin page
     */
    add_filter('plugin_action_links_'.plugin_basename(__FILE__),array('Intrackt\CompanionProducts\Manager','actionLinksLeft'));
    add_filter('plugin_row_meta',array('Intrackt\CompanionProducts\Manager','actionLinksRight'),10,2);

    /*
     * activate the Manager singleton
     */
    add_action( 'init', array( 'Intrackt\CompanionProducts\Manager', 'init' ) );

    /*
     * set the location of the code that activates the CompanionProducts plugin
     */
    register_activation_hook( __FILE__, array( '\Intrackt\CompanionProducts\Manager', 'activate' ) );

    /*
     * set up the process to automatically go to the main CompanionProducts page as the activation welcome page
     */
    add_action( 'admin_init', array( '\Intrackt\CompanionProducts\Manager', 'welcome' ) );

    /*
     * Add the logic to create the menu if CompanionProducts is activated
     */
    add_action('admin_menu', array( '\Intrackt\CompanionProducts\Manager', 'menuCreate' ));

    /*
     * set the location of the code that deactivates the CompanionProducts plugin
     */
    register_deactivation_hook( __FILE__, array( '\Intrackt\CompanionProducts\Manager', 'deactivate' ) );

    /*
     * add filter to add content to the intrackt common menu page
     */
    add_filter('intract_commonpage_body', array( '\Intrackt\CompanionProducts\Common', 'displayBody' ));

}

/*
 * For this plugin
 */
{
    /*
     * load the source for any required classes or files this plugin
     */
    require_once( INTRACKT_COMPANIONPRODUCTS_PLUGIN_DIR . 'class.Intrackt-Companion-Products-Actions.php' );

    /*
     * Add custom field to a product
     */
    //add_action('edit_form_advanced',array('\Intrackt\CompanionProducts\Actions','adminPostEdit'));
    add_filter('woocommerce_product_data_tabs',array('\Intrackt\CompanionProducts\Actions','enableAdminProductTab'));
    add_filter('woocommerce_product_data_panels',array('\Intrackt\CompanionProducts\Actions','adminPostEdit'));

    /*
     * Add action to form for hidden companion product fields.
     */
    add_action('woocommerce_after_add_to_cart_button',array( '\Intrackt\CompanionProducts\Actions', 'addHiddenCompanionProductFields' ));
    
    /*
     * Add action upon press of add to cart button (going to the template).
     */
    add_action('template_redirect',array('\Intrackt\CompanionProducts\Actions','addHiddenCompanionProductToCartA' ));
    add_action('woocommerce_add_to_cart',array('\Intrackt\CompanionProducts\Actions','addHiddenCompanionProductToCartB'),10,6);
    
    /*
     * Add filter for item data when added to cart.
     */
    add_filter('woocommerce_add_cart_item_data',array( '\Intrackt\CompanionProducts\Actions', 'updateSkuDataInCart' ),10,4);
    
    /*
     * Add action after all added to cart and before display
     */
    add_action('woocommerce_cart_loaded_from_session',array( '\Intrackt\CompanionProducts\Actions', 'cartLoadedFromSession' ),999);
    
    /*
     * eliminate cart remove link/button for companion products
     */
    add_filter('woocommerce_cart_item_remove_link',array( '\Intrackt\CompanionProducts\Actions', 'removeCartCPRemoveLink' ),999,2);
    
    /*
     * eliminate ability to change quantity for companion products
     */
    add_filter('woocommerce_cart_item_quantity',array( '\Intrackt\CompanionProducts\Actions', 'disableCartCPChangeQty' ),999,3);
    
    /*
     * Add action when item removed from cart
     */
    add_action('woocommerce_cart_item_removed',array( '\Intrackt\CompanionProducts\Actions', 'removedCartItem' ),10,2);
    
    /*
     * Add action to add a "Remove All Items" button to cart
     */
    add_action('woocommerce_cart_actions',array( '\Intrackt\CompanionProducts\Actions', 'removedAllItemsButton' ));
    
    /*
     * Add action at bottom of front pages
     */
    add_action('wp_footer',array( '\Intrackt\CompanionProducts\Actions', 'bottomOfFrontPage' ));
    
    /*
     * add action to handle processing of any other actions on any page
     */
    add_action('wp_footer',array('\Intrackt\CompanionProducts\Actions','preventReload'),1);
    
    /*
     * add custom column for companion product
     */
    add_filter('manage_product_posts_columns',array('\Intrackt\CompanionProducts\Actions','customAdminPostColumnHead'));
    add_action('manage_product_posts_custom_column',array('\Intrackt\CompanionProducts\Actions','customAdminPostColumnData'),10,2);
    
    /*
     * permit duplicate skus
     */
    //add_filter('wc_product_has_unique_sku','__return_false');
    add_filter('wc_product_has_unique_sku',array ( '\Intrackt\CompanionProducts\Actions', 'productDuplicateSkus' ),10,3);

    /*
     * Add action to remove companion product when product is copied
     */
    add_filter('woocommerce_product_duplicate', array ( '\Intrackt\CompanionProducts\Actions', 'productDuplicated' ), 10, 2 );
    
    /*
     * add filter to update new custom field on post save
     */
    add_action('save_post_product',array('\Intrackt\CompanionProducts\Actions','actionSavePostProduct'),10,3);

    /*
     * add action when getting ready to compute cart totals
     */
    //add_filter('woocommerce_product_get_price',array('\Intrackt\CompanionProducts\Actions','filterBeforeCalculateTotals'),10,2);
    add_action('woocommerce_before_calculate_totals',array('\Intrackt\CompanionProducts\Actions','actionBeforeCalculateTotals'), 10, 1);

    /*
     * add filters to display companion product prices as blank in cart
     */
    add_filter('woocommerce_cart_item_price',array('\Intrackt\CompanionProducts\Actions','blankCompanionProductPrice'),10,3);
    add_filter('woocommerce_cart_item_subtotal',array('\Intrackt\CompanionProducts\Actions','blankCompanionProductPrice'),10,3);
    add_filter('woocommerce_order_formatted_line_subtotal',array('\Intrackt\CompanionProducts\Actions','blankCompanionProductPrice'),10,3);
    
    /*
     * support added for woocommerce-side-cart-premium
     */
    {
        /*
         * intercept start of building side cart data for one of the products
         */
        add_filter('woocommerce_cart_item_permalink',array('\Intrackt\CompanionProducts\Actions','wscpFilterCartItem'),10,3);
        
        /*
         * add code after image on side cart
         */
        add_action('xoo_wsc_after_product_image',array('\Intrackt\CompanionProducts\Actions','wscpActionAfterImage'),10,0);
        
        /*
         * Adjust companion values after qty change (really only needed with side cart
         */
        add_action('woocommerce_after_cart_item_quantity_update',array('\Intrackt\CompanionProducts\Actions','afterCartQtyChange'),10,4);
        
        /*
         * Add action at bottom of front pages
         */
        add_action('wp_footer',array('\Intrackt\CompanionProducts\Actions','wscpActionFrontPageFooter'));
    
    }

}
    
