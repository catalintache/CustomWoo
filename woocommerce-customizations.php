<?php
/*
Plugin Name: WooCommerce Customizations
Plugin URI:  https://github.com/username/catalintache
Description: Personalizări pentru WooCommerce – discounturi dinamice, restricții pentru Shop Manager, calcule de livrare, statusuri custom, notificări WhatsApp și ajustări de cantitate.
Version:     2.0
Author:      Catalin Tache
Author URI:  https://www.linkedin.com/in/catalintache/
License:     GPL2
Text Domain: woocommerce-customizations
*/

// Ieșire dacă nu se accesează prin WordPress.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include modulele
require_once plugin_dir_path( __FILE__ ) . 'includes/discount-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/warehouse-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/delivery-methods.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/order-statuses.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/whatsapp-notifications.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/quantity-customizations.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/checkout-notices.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/globals.php';
