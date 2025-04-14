<?php
// includes/order-statuses.php

// Înregistrează statusul "Plătită cu cardul"
function register_card_paid_order_status() {
    register_post_status( 'wc-card-paid', array(
        'label'                     => 'Plătită cu cardul',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Plătită cu cardul (%s)', 'Plătită cu cardul (%s)' )
    ) );
}
add_action( 'init', 'register_card_paid_order_status' );

function add_card_paid_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-card-paid'] = 'Plătită cu cardul';
        }
    }
    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_card_paid_to_order_statuses' );

// Înregistrează statusul "Preluată"
function register_preluata_order_status() {
    register_post_status( 'wc-preluata', array(
        'label'                     => 'Preluată',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Preluată (%s)', 'Preluată (%s)' )
    ) );
}
add_action( 'init', 'register_preluata_order_status' );

function add_preluata_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-preluata'] = 'Preluată';
        }
    }
    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_preluata_to_order_statuses' );

// Înregistrează statusul "Plata OP în așteptare"
function register_op_pending_order_status() {
    register_post_status( 'wc-op-pending', array(
        'label'                     => 'Plata OP în așteptare',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Plata OP în așteptare (%s)', 'Plata OP în așteptare (%s)' )
    ) );
}
add_action( 'init', 'register_op_pending_order_status' );

function add_op_pending_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-op-pending'] = 'Plata OP în așteptare';
        }
    }
    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_op_pending_to_order_statuses' );

// Înregistrează statusul "Plătită prin OP"
function register_op_paid_order_status() {
    register_post_status( 'wc-op-paid', array(
        'label'                     => 'Plătită prin OP',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Plătită prin OP (%s)', 'Plătită prin OP (%s)' )
    ) );
}
add_action( 'init', 'register_op_paid_order_status' );

function add_op_paid_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-op-paid'] = 'Plătită prin OP';
        }
    }
    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_op_paid_to_order_statuses' );

// La finalizarea comenzii: pentru plata COD se setează implicit "Plata OP în așteptare"
function my_change_op_order_status( $order_id ) {
    if ( ! $order_id ) return;
    $order = wc_get_order( $order_id );
    if ( 'cod' === $order->get_payment_method() && $order->has_status( 'pending' ) ) {
        $order->update_status( 'op-pending', 'Setare implicită: Plata OP în așteptare' );
    }
}
add_action( 'woocommerce_thankyou', 'my_change_op_order_status', 10, 1 );

// Pentru comenzile cu alte metode de plată se setează "Plătită cu cardul" când statusul este "processing"
function set_default_card_paid_order_status( $order_id ) {
    if ( ! $order_id ) return;
    $order = wc_get_order( $order_id );
    if ( 'cod' !== $order->get_payment_method() && $order->has_status( 'processing' ) ) {
        $order->update_status( 'card-paid', 'Setare implicită: Plătită cu cardul' );
    }
}
add_action( 'woocommerce_thankyou', 'set_default_card_paid_order_status', 10, 1 );
