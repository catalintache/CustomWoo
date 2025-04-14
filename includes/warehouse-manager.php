<?php
// includes/warehouse-manager.php

// Redirect pentru shop_manager după login.
function custom_shop_manager_login_redirect( $redirect, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) && in_array( 'shop_manager', (array) $user->roles ) ) {
        return admin_url( 'edit.php?post_type=shop_order' );
    }
    return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'custom_shop_manager_login_redirect', 10, 2 );

// Restricționarea accesului în wp-admin pentru shop_manager.
function restrict_shop_manager_admin_access() {
    if ( current_user_can( 'shop_manager' ) && ! defined( 'DOING_AJAX' ) ) {
        $current_uri = $_SERVER['REQUEST_URI'];
        $allowed_substrings = array(
            'edit.php?post_type=shop_order',
            'admin.php?page=wc-orders',
            'admin-ajax.php'
        );
        $allowed = false;
        foreach ( $allowed_substrings as $substr ) {
            if ( false !== strpos( $current_uri, $substr ) ) {
                $allowed = true;
                break;
            }
        }
        if ( ! $allowed ) {
            wp_redirect( admin_url( 'edit.php?post_type=shop_order' ) );
            exit;
        }
    }
}
add_action( 'admin_init', 'restrict_shop_manager_admin_access' );

// Eliminarea meniurilor în wp-admin pentru shop_manager.
function custom_shop_manager_remove_menu_items() {
    if ( current_user_can( 'shop_manager' ) ) {
        global $menu;
        foreach ( $menu as $key => $item ) {
            if ( isset( $item[2] ) && $item[2] !== 'edit.php?post_type=shop_order' ) {
                unset( $menu[ $key ] );
            }
        }
    }
}
add_action( 'admin_menu', 'custom_shop_manager_remove_menu_items', 999 );

// Personalizarea barei de administrare (Admin Bar) pentru shop_manager.
function custom_shop_manager_admin_bar( $wp_admin_bar ) {
    if ( current_user_can( 'shop_manager' ) ) {
        $wp_admin_bar->remove_node( 'wp-logo' );
        $wp_admin_bar->remove_node( 'site-name' );
        $wp_admin_bar->remove_node( 'updates' );
        $wp_admin_bar->remove_node( 'comments' );
        $wp_admin_bar->remove_node( 'new-content' );
        $wp_admin_bar->remove_node( 'my-account' );
        $wp_admin_bar->add_node( array(
            'id'    => 'orders',
            'title' => 'Comenzi',
            'href'  => admin_url( 'edit.php?post_type=shop_order' )
        ) );
    }
}
add_action( 'admin_bar_menu', 'custom_shop_manager_admin_bar', 999 );

// Limitarea acțiunilor de schimbare a statusului comenzilor pentru shop_manager.
function custom_shop_manager_order_status_actions( $actions, $order ) {
    if ( current_user_can( 'shop_manager' ) ) {
        $allowed_statuses = array(
            'wc-processing' => 'Preluată',
            'wc-completed'  => 'Finalizată',
        );
        foreach ( $actions as $action_key => $action ) {
            if ( ! array_key_exists( $action_key, $allowed_statuses ) ) {
                unset( $actions[ $action_key ] );
            }
        }
    }
    return $actions;
}
add_filter( 'woocommerce_admin_order_actions', 'custom_shop_manager_order_status_actions', 10, 2 );

// Blocarea editării comenzilor finalizate pentru shop_manager.
function custom_shop_manager_prevent_edit_completed( $allcaps, $caps, $args ) {
    if ( isset( $args[0] ) && $args[0] === 'edit_shop_orders' && current_user_can( 'shop_manager' ) ) {
        $order_id = isset( $args[2] ) ? $args[2] : null;
        if ( $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order && $order->get_status() === 'completed' ) {
                $allcaps['edit_shop_orders'] = false;
            }
        }
    }
    return $allcaps;
}
add_filter( 'user_has_cap', 'custom_shop_manager_prevent_edit_completed', 10, 3 );

// Redirect din frontend pentru shop_manager.
function custom_shop_manager_frontend_redirect() {
    if ( current_user_can( 'shop_manager' ) && ! is_admin() ) {
        wp_redirect( admin_url( 'edit.php?post_type=shop_order' ) );
        exit;
    }
}
add_action( 'template_redirect', 'custom_shop_manager_frontend_redirect' );

// Ascunderea barei de administrare în frontend pentru shop_manager.
function custom_shop_manager_hide_admin_bar() {
    if ( current_user_can( 'shop_manager' ) && ! is_admin() ) {
        show_admin_bar( false );
    }
}
add_action( 'after_setup_theme', 'custom_shop_manager_hide_admin_bar' );
