<?php
// includes/quantity-customizations.php

// Pentru produsele cu ID-uri specifice (ex. brichete)
function custom_enqueue_quantity_script() {
    if ( is_product() ) {
        $product_id = get_the_ID();
        if ( in_array( $product_id, array( 16231, 16236 ) ) ) {
            wp_enqueue_script(
                'custom-quantity-step',
                get_stylesheet_directory_uri() . '/js/custom-quantity-step.js',
                array('jquery'),
                '1.0',
                true
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_quantity_script' );

// Pentru produsele din categoria 73 (peleti)
function custom_enqueue_quantity_script_cat73() {
    if ( is_product() && has_term( 73, 'product_cat' ) ) {
        wp_dequeue_script('custom-quantity-step');
        wp_enqueue_script(
            'custom-quantity-step-cat73',
            get_stylesheet_directory_uri() . '/js/custom-quantity-step-cat73.js',
            array('jquery'),
            '1.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_quantity_script_cat73', 20 );

// Personalizare câmp de cantitate în coș
add_filter('woocommerce_cart_item_quantity', 'custom_cart_quantity_input', 99, 3);
function custom_cart_quantity_input($product_quantity, $cart_item, $cart_item_key) {
    if ( ! isset($cart_item['data']) || ! is_object($cart_item['data']) ) {
        return $product_quantity;
    }
    $product = $cart_item['data'];
    if ( ! method_exists($product, 'get_id') ) {
        return $product_quantity;
    }
    $product_id = $product->get_id();

    $increment    = 1;
    $min_value    = 1;
    $step_value   = 1;
    $button_plus  = '+';
    $button_minus = '-';

    if ( has_term(73, 'product_cat', $product_id) ) {
        $increment    = 66;
        $min_value    = 66;
        $step_value   = 66;
        $button_plus  = '+1 palet';
        $button_minus = '-1 palet';
    }
    elseif ( in_array($product_id, array(16231, 16236)) ) {
        $increment    = 40;
        $min_value    = 40;
        $step_value   = 40;
        $button_plus  = '+1 palet';
        $button_minus = '-1 palet';
    }

    $qty = $cart_item['quantity'];

    $html  = '<div class="quantity">';
    $html .= '<button type="button" class="minus">' . esc_html($button_minus) . '</button>';
    $html .= '<input type="number" class="input-text qty" name="cart[' . esc_attr($cart_item_key) . '][qty]" value="' . esc_attr($qty) . '" min="' . esc_attr($min_value) . '" step="' . esc_attr($step_value) . '" data-increment="' . esc_attr($increment) . '" data-min="' . esc_attr($min_value) . '" />';
    $html .= '<button type="button" class="plus">' . esc_html($button_plus) . '</button>';
    $html .= '</div>';

    return $html;
}

function custom_enqueue_cart_script() {
    if ( is_cart() ) {
        wp_enqueue_script( 
            'custom-cart-quantity', 
            get_stylesheet_directory_uri() . '/js/cart-qty.js', 
            array('jquery'), 
            '1.0', 
            true 
        );
    }
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_cart_script' );
