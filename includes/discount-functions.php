<?php
// includes/discount-functions.php

// Helper: Returnează ID-ul folosit pentru mapping – dacă produsul este o variație, se folosește ID-ul părintelui.
function get_discount_product_id( $product ) {
    if ( $product->is_type( 'variation' ) ) {
        return intval( $product->get_parent_id() );
    }
    return intval( $product->get_id() );
}

// Funcție pentru a returna discount tiers (în format string) pe baza ID-ului produsului.
function get_discount_tiers_by_product_id( $product_id ) {
    $product_id = intval( $product_id );
    $product_discount_mapping = array(
         16209 => 1,
         16211 => 3,
         16934 => 3,
         16220 => 3,
         16224 => 2,
         16927 => 3,
         16939 => 2,
         20112 => 1,
         20106 => 1,
         16152 => 1,
         // Adaugă aici alte asocieri după necesitate...
    );
    
    if ( ! array_key_exists( $product_id, $product_discount_mapping ) ) {
         return '';
    }
    
    $tier = $product_discount_mapping[$product_id];
    
    $discount_tiers_config = array( 
         1 => "11:10,31:15",
         2 => "10:10,40:15,120:25",
         3 => "10:10,30:15,120:25",
    );
    
    return isset( $discount_tiers_config[$tier] ) ? $discount_tiers_config[$tier] : '';
}

// (1.1) Filtru pentru afișarea prețului cu datele de discount.
add_filter( 'woocommerce_get_price_html', 'custom_price_html_with_discount_data', 10, 2 );
function custom_price_html_with_discount_data( $price_html, $product ) {
    $base_price = $product->get_regular_price();
    $mapping_id = get_discount_product_id( $product );
    $discount_tiers = get_discount_tiers_by_product_id( $mapping_id );
    
    if ( empty( $discount_tiers ) ) {
        return $price_html;
    }
    
    $tiers_array = array();
    $pairs = explode( ',', $discount_tiers );
    foreach ( $pairs as $pair ) {
        $parts = explode( ':', $pair );
        if ( count( $parts ) == 2 ) {
            $qty = intval( trim( $parts[0] ) );
            $discount_percent = floatval( trim( $parts[1] ) );
            $factor = 1 - ( $discount_percent / 100 );
            $tiers_array[ $qty ] = $factor;
        }
    }
    ksort( $tiers_array, SORT_NUMERIC );
    $json_tiers = json_encode( $tiers_array );
    $new_price_html = '<span class="product-price" data-base-price="' . esc_attr( $base_price ) . '" data-discount-tiers=\'' . esc_attr( $json_tiers ) . '\'>' . $price_html . '</span>';
    return $new_price_html;
}

// (1.2) Afișarea tabelului de discount pe pagina produsului.
add_action( 'woocommerce_single_product_summary', 'display_custom_discount_table', 25 );
function display_custom_discount_table() {
    global $product;
    if ( ! $product ) {
        $product = wc_get_product( get_the_ID() );
    }
    if ( ! $product ) {
        return;
    }
    
    $mapping_id = get_discount_product_id( $product );
    $discount_tiers = get_discount_tiers_by_product_id( $mapping_id );
    if ( empty( $discount_tiers ) ) {
        return;
    }
    
    $tiers_array = array();
    $pairs = explode( ',', $discount_tiers );
    foreach ( $pairs as $pair ) {
        $parts = explode( ':', $pair );
        if ( count( $parts ) == 2 ) {
            $qty = intval( trim( $parts[0] ) );
            $discount_percent = floatval( trim( $parts[1] ) );
            $tiers_array[ $qty ] = $discount_percent;
        }
    }
    ksort( $tiers_array, SORT_NUMERIC );
    
    $base_price = $product->get_regular_price();
    $thresholds = array_keys( $tiers_array );
    $discount_values = array_values( $tiers_array );
    
    $rows = array();
    $first_threshold = $thresholds[0];
    $rows[] = array(
         'range'    => '1 - ' . ( $first_threshold - 1 ),
         'discount' => '0%',
         'price'    => number_format( $base_price, 2 ) . ' lei',
         'min'      => 1,
         'max'      => $first_threshold - 1,
    );
    
    $count = count( $thresholds );
    for ( $i = 0; $i < $count; $i++ ) {
         $current_threshold = $thresholds[ $i ];
         $current_discount = $discount_values[ $i ];
         $factor = 1 - ( $current_discount / 100 );
         if ( $i < $count - 1 ) {
            $next_threshold = $thresholds[ $i + 1 ];
            $range_text = $current_threshold . ' - ' . ( $next_threshold - 1 );
            $min_val = $current_threshold;
            $max_val = $next_threshold - 1;
         } else {
            $range_text = $current_threshold . ' sau mai mult';
            $min_val = $current_threshold;
            $max_val = 9999;
         }
         $discounted_price = $base_price * $factor;
         $rows[] = array(
             'range'    => $range_text,
             'discount' => $current_discount . '%',
             'price'    => number_format( $discounted_price, 2 ) . ' lei',
             'min'      => $min_val,
             'max'      => $max_val,
         );
    }
    
    echo '<div class="custom-discount-table" style="margin-top:20px;">';
    echo '<table style="width:100%; border-collapse: collapse;" border="1">';
    echo '<thead><tr><th>Saci</th><th>Preț sac</th><th>Discount</th></tr></thead>';
    echo '<tbody>';
    foreach ( $rows as $row ) {
       echo '<tr data-min="' . esc_attr( $row['min'] ) . '" data-max="' . esc_attr( $row['max'] ) . '">';
       echo '<td>' . esc_html( $row['range'] ) . '</td>';
       echo '<td>' . esc_html( $row['price'] ) . '</td>';
       echo '<td>' . esc_html( $row['discount'] ) . '</td>';
       echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// (1.3) Actualizarea prețului în coș în funcție de cantitate și discount.
add_action( 'woocommerce_before_calculate_totals', 'apply_dynamic_discount_to_cart', 10, 1 );
function apply_dynamic_discount_to_cart( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
    
    foreach ( $cart->get_cart() as $cart_item ) {
         $product = $cart_item['data'];
         $base_price = $product->get_regular_price();
         $mapping_id = get_discount_product_id( $product );
         $discount_tiers = get_discount_tiers_by_product_id( $mapping_id );
         $tiers_array = array();
         if ( ! empty( $discount_tiers ) ) {
            $pairs = explode( ',', $discount_tiers );
            foreach ( $pairs as $pair ) {
              $parts = explode( ':', $pair );
              if ( count( $parts ) == 2 ) {
                  $qty_threshold = intval( trim( $parts[0] ) );
                  $discount_percent = floatval( trim( $parts[1] ) );
                  $factor = 1 - ( $discount_percent / 100 );
                  $tiers_array[ $qty_threshold ] = $factor;
              }
            }
            ksort( $tiers_array, SORT_NUMERIC );
         }
         $current_qty = $cart_item['quantity'];
         $discountFactor = 1;
         foreach ( $tiers_array as $threshold => $factor ) {
             if ( $current_qty >= $threshold ) {
                 $discountFactor = $factor;
             }
         }
         $new_price = $base_price * $discountFactor;
         $cart_item['data']->set_price( $new_price );
    }
}

// (1.4) Încarcă fișierul JavaScript pentru actualizarea dinamică a prețului.
function load_custom_discount_script() {
    wp_enqueue_script(
        'custom-discount-script',
        get_stylesheet_directory_uri() . '/js/custom-discount.js',
        array('jquery'),
        '1.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'load_custom_discount_script' );

// (1.5) Shortcode pentru a afișa tabelul de discount.
function custom_discount_table_shortcode() {
    ob_start();
    display_custom_discount_table();
    return ob_get_clean();
}
add_shortcode('custom_discount_table', 'custom_discount_table_shortcode');
