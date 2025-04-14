<?php
// includes/delivery-methods.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definește adresa depozitului.
if ( ! defined( 'STORE_ADDRESS' ) ) {
    define( 'STORE_ADDRESS', 'YOUR_STORE_ADDRESS ' );
}

// Funcția calculezDist() – returnează distanța în km.
if ( ! function_exists( 'calculezDist' ) ) {
    function calculezDist( $origin, $destination ) {
        $api_key = 'YOUR_API_KEY';
        $origins = urlencode( $origin );
        $destinations = urlencode( $destination );
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origins}&destinations={$destinations}&key={$api_key}&units=metric&mode=driving&language=ro";
        
        $response = wp_remote_get( $url );
        if ( is_wp_error( $response ) ) {
            error_log( "Google API error: " . $response->get_error_message() );
            return 0;
        }
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( ! isset( $data['rows'][0]['elements'][0]['distance']['value'] ) ) {
            error_log( "Distance not found in API response: " . $body );
            return 0;
        }
        $distance_meters = $data['rows'][0]['elements'][0]['distance']['value'];
        return floatval( $distance_meters ) / 1000;
    }
}

// Obține datele de checkout.
function get_checkout_post_data() {
    $data = array();
    if ( isset( $_POST['post_data'] ) ) {
        parse_str( $_POST['post_data'], $data );
    } else {
        $data = $_POST;
    }
    return $data;
}

// Transformă ID-ul județului în nume.
function get_state_name( $state ) {
    if ( is_numeric( $state ) && isset( $GLOBALS['mapare_judet'][ $state ]['judet'] ) ) {
        return $GLOBALS['mapare_judet'][ $state ]['judet'];
    }
    return $state;
}

// Actualizează greutatea totală a coșului și salvează în sesiune.
function update_cart_total_weight( $cart ) {
    if ( ! is_object( $cart ) ) {
        $cart = WC()->cart;
    }
    
    $total_weight = 0;
    foreach ( $cart->get_cart() as $cart_item ) {
        $w = floatval( $cart_item['data']->get_weight() );
        if ( $w <= 0 ) {
            $w = 1;
        }
        $total_weight += $w * intval( $cart_item['quantity'] );
    }
    WC()->session->set( 'total_order_weight', $total_weight );
}
add_action( 'woocommerce_cart_updated', 'update_cart_total_weight' );

// Determină metoda de livrare și calculează distanța și greutatea.
function determine_delivery_method( $cart ) {
    $post_data = get_checkout_post_data();
    $override = isset( $post_data['override_metoda'] ) ? sanitize_text_field( $post_data['override_metoda'] ) : 'auto';
    $billing_state   = ! empty( $post_data['billing_state'] ) ? sanitize_text_field( $post_data['billing_state'] ) : WC()->customer->get_billing_state();
    $billing_address = ! empty( $post_data['billing_address_1'] ) ? sanitize_text_field( $post_data['billing_address_1'] ) : WC()->customer->get_billing_address_1();
    $municipality    = ! empty( $post_data['billing_city'] ) ? sanitize_text_field( $post_data['billing_city'] ) : WC()->customer->get_billing_city();

    if ( !isset($billing_state) || strlen($billing_state) === 0 || empty( $billing_address ) || empty( $municipality ) ) {
        WC()->session->set( 'livrare_metoda', 'ridicare' );
        WC()->session->set( 'calculated_distance', 0 );
        return array(
            'method'           => 'ridicare',
            'distance'         => 0,
            'zone'             => 0,
            'order_weight'     => 0,
            'computed_method'  => 'transport_propriu'
        );
    }
    
    $state = get_state_name( $billing_state );
    $destination = $billing_address . ', ' . $municipality . ', ' . $state;
    $origin = STORE_ADDRESS;
    error_log( "Destination address: $destination" );
    
    $distance = floatval( calculezDist( $origin, $destination ) );
    error_log( "Calculated distance: $distance km" );
    
    $order_weight = WC()->session->get( 'total_order_weight' );
    if ( empty( $order_weight ) ) {
        $order_weight = 0;
        foreach ( $cart->get_cart() as $cart_item ) {
            $w = floatval( $cart_item['data']->get_weight() );
            if ( $w <= 0 ) { $w = 1; }
            $order_weight += $w * intval( $cart_item['quantity'] );
        }
        WC()->session->set( 'total_order_weight', $order_weight );
    }
    error_log( "Total order weight: $order_weight kg" );
    
    $computed_method = ( $distance <= 100 ) ? 'transport_propriu' : ( ( $order_weight < 30 ) ? 'curier_sameday' : 'pallex' );
    $final_method = ( $override === 'ridicare' ) ? 'ridicare' : $computed_method;
    WC()->session->set( 'livrare_metoda', $final_method );
    WC()->session->set( 'calculated_distance', $distance );
    
    return array(
        'method'           => $final_method,
        'distance'         => $distance,
        'zone'             => 0,
        'order_weight'     => $order_weight,
        'computed_method'  => $computed_method
    );
}

// Mapare cod metodă → text de afișare.
function get_delivery_method_text( $method_code, $computed_method = '' ) {
    $map = array(
        'ridicare'          => '📍 Ridicare din depozit',
        'transport_propriu' => '🚚 Livrare prin A.R.D.E.',
        'curier_sameday'    => '🚚 Livrare prin Curier',
        'pallex'            => '🚛 Livrare prin Pallex',
    );

    if ( $method_code === 'auto' && ! empty( $computed_method ) ) {
        return isset( $map[ $computed_method ] ) ? $map[ $computed_method ] : $computed_method;
    }
    return isset( $map[ $method_code ] ) ? $map[ $method_code ] : $method_code;
}

// Funcția de calcul pentru "transport_propriu".
function calculate_transport_propriu_fee( $cart, $distance ) {
    $total_paleti = 0;

    foreach ( $cart->get_cart() as $cart_item ) {
        $quantity = intval( $cart_item['quantity'] );
        $nr_paleti = 0;
        $found = false;
        
        if ( isset( $GLOBALS['produse'] ) && is_array( $GLOBALS['produse'] ) ) {
            foreach ( $GLOBALS['produse'] as $produs ) {
                if ( $produs['id'] == $cart_item['product_id'] ) {
                    $greutate_unitar = floatval( $produs['greutate_unitar'] );
                    $greutate_palet  = floatval( $produs['greutate_palet'] );
                    if ( $greutate_unitar <= 0 ) {
                        $greutate_unitar = 1;
                    }
                    if ( $greutate_palet <= 0 ) {
                        $greutate_palet = 1000;
                    }
                    $units_per_palet = $greutate_palet / $greutate_unitar;
                    $nr_paleti = ceil( $quantity / $units_per_palet );
                    $found = true;
                    break;
                }
            }
        }
        if ( ! $found || $nr_paleti == 0 ) {
            $w = floatval( $cart_item['data']->get_weight() );
            if ( $w <= 0 ) {
                $w = 1;
            }
            $nr_paleti = ceil( ($w * $quantity) / 1000 );
        }
        $total_paleti += $nr_paleti;
    }

    if ( $total_paleti > 5 ) {
        WC()->session->set( 'shipping_over_5', true );
        error_log("Pentru comenzi de peste 5 paleți se va contacta pentru cotație de transport.");
        return 0;
    } else {
        WC()->session->set( 'shipping_over_5', false );
    }

    $fee_net = 0;
    if ( $distance < 15 ) {
        if ( (int)$total_paleti === 1 ) {
            $fee_net = 59;
        } else {
            $fee_net = 118;
        }
    }
    elseif ( $distance >= 15 && $distance < 21 ) {
        if ( (int)$total_paleti === 1 ) {
            $fee_net = 5 * $distance;
        } else {
            $fee_net = 5 * $distance * 2;
        }
    }
    elseif ( $distance >= 21 && $distance < 31 ) {
        if ( (int)$total_paleti === 1 ) {
            $fee_net = 3.5 * $distance;
        } else {
            $fee_net = 3.5 * $distance * 2;
        }
    }
    elseif ( $distance >= 31 && $distance < 41 ) {
        if ( (int)$total_paleti === 1 ) {
            $fee_net = 3.5 * $distance;
        } else {
            $fee_net = 3.25 * $distance * 2;
        }
    }
    elseif ( $distance >= 41 && $distance < 51 ) {
        if ( (int)$total_paleti === 1 || (int)$total_paleti === 2 ) {
            $fee_net = 2.8 * $distance * 2;
        } elseif ( (int)$total_paleti >= 3 ) {
            $fee_net = 2.5 * $distance * 2;
        }
    }
    elseif ( $distance >= 51 && $distance < 61 ) {
        if ( (int)$total_paleti === 1 || (int)$total_paleti === 2 ) {
            $fee_net = 2.6 * $distance * 2;
        } elseif ( (int)$total_paleti >= 3 ) {
            $fee_net = 2.45 * $distance * 2;
        }
    }
    elseif ( $distance >= 61 && $distance <= 100 ) {
        if ( (int)$total_paleti === 1 || (int)$total_paleti === 2 ) {
            $fee_net = 2.3 * $distance * 2;
        } elseif ( (int)$total_paleti >= 3 ) {
            $fee_net = 2.0 * $distance * 2;
        }
    }
    else {
        error_log("Distanță în afara intervalelor de tarifare definite.");
        $fee_net = 0;
    }

    error_log("Transport ARDE fee (net): $fee_net (Total paleți: $total_paleti)");
    $tva = 0.19;
    $fee_with_tva = $fee_net * (1 + $tva);
    error_log("Transport ARDE fee (TVA inclus): $fee_with_tva");

    return $fee_with_tva;
}

// Funcția de calcul pentru "pallex" (se aplică pentru distanțe > 100 km).
function calculate_pallex_fee( $cart, $distance, $region ) {
    if ( $distance <= 100 ) {
        return 0;
    }
    
    $post_data = get_checkout_post_data();
    $municipality = !empty($post_data['municipality']) ? sanitize_text_field($post_data['municipality']) : '';
    
    $region_key = strtoupper($region);
    $origin_zone = '';
    $state_name  = '';
    foreach ( $GLOBALS['mapare_judet'] as $entry ) {
        if ( strtoupper($entry['judet']) === $region_key ) {
            $origin_zone = $entry['capitala_judet'] . ', ' . $entry['judet'] . ', Romania';
            $state_name  = $entry['judet'];
            break;
        }
    }
    if ( empty($origin_zone) ) {
        $origin_zone = STORE_ADDRESS;
    }
    
    $destination_zone = $municipality . ', ' . $state_name . ', Romania';
    $zona_distance = floatval( calculezDist( $origin_zone, $destination_zone ) );
    
    if ( $zona_distance > 20 && $zona_distance <= 80 ) {
        $zona_extra = 'zona2';
    } elseif ( $zona_distance > 80 ) {
        $zona_extra = 'zona3';
    } else {
        $zona_extra = 'zona1';
    }
    
    $fee = 0;
    $tva_rate = 0.19;
    
    foreach ( $cart->get_cart() as $cart_item ) {
        $product_id = intval($cart_item['product_id']);
        $quantity   = intval($cart_item['quantity']);
        
        $nr_paleti = 0;
        $found = false;
        $incadrare = '';
        $greutate_unitar = 0;
        $greutate_palet  = 0;
        
        if ( isset($GLOBALS['produse']) && is_array($GLOBALS['produse']) ) {
            foreach ($GLOBALS['produse'] as $produs) {
                if ( intval($produs['id']) === $product_id ) {
                    $greutate_unitar = floatval($produs['greutate_unitar']);
                    $greutate_palet  = floatval($produs['greutate_palet']);
                    if ($greutate_unitar <= 0) { 
                        $greutate_unitar = 1; 
                    }
                    if ($greutate_palet <= 0) { 
                        $greutate_palet = 1000; 
                    }
                    $units_per_palet = $greutate_palet / $greutate_unitar;
                    $nr_paleti = ceil($quantity / $units_per_palet);
                    $incadrare = strtolower(trim($produs['incadrare']));
                    $found = true;
                    break;
                }
            }
        }
        
        if (!$found || $nr_paleti == 0) {
            $w = floatval($cart_item['data']->get_weight());
            if ($w <= 0) { 
                $w = 1; 
            }
            $greutate_palet = 1000;
            $units_per_palet = $greutate_palet / $w;
            $nr_paleti = ceil($quantity / $units_per_palet);
            $total_weight = $w * $quantity;
            if ($total_weight < 100) {
                $incadrare = 'mini quarter';
            } elseif ($total_weight < 200) {
                $incadrare = 'quarter';
            } elseif ($total_weight <= 400) {
                $incadrare = 'fl';
            } elseif ($total_weight < 800) {
                $incadrare = 'full';
            } else {
                $incadrare = 'megafull';
            }
        }
        
        $rez_transport = isset($GLOBALS['calcul_transport'][$region_key]) ? $GLOBALS['calcul_transport'][$region_key] : [];
        if (empty($rez_transport)) {
            continue;
        }
        
        $tariff_key = '';
        switch ($incadrare) {
            case 'regula20':
            case 'full light':
            case 'mixt':
                if ($nr_paleti == 1) {
                    $tariff_key = 'fl1';
                } else {
                    $tariff_key = 'fl23';
                }
                break;
            case 'full':
                if ($nr_paleti == 1) {
                    $tariff_key = 'full1';
                } else {
                    $tariff_key = 'full23';
                }
                break;
            case 'half':
                $tariff_key = 'half';
                break;
            case 'megafull':
                if ($greutate_palet > 1000) {
                    if ($nr_paleti <= 3) {
                        $tariff_key = 'mfmax13';
                    } else {
                        $tariff_key = 'mfmax4';
                    }
                } else {
                    if ($nr_paleti <= 3) {
                        $tariff_key = 'mf13';
                    } else {
                        $tariff_key = 'mf4';
                    }
                }
                break;
            default:
                if ($greutate_palet < 100) {
                    $tariff_key = 'mini quarter';
                } elseif ($greutate_palet < 200) {
                    $tariff_key = 'quarter';
                } elseif ($greutate_palet <= 400) {
                    $tariff_key = 'fl23';
                } elseif ($greutate_palet <= 800) {
                    $tariff_key = 'full23';
                } elseif ($greutate_palet <= 1000) {
                    $tariff_key = 'mf13';
                } elseif ($greutate_palet <= 1200) {
                    $tariff_key = 'mfmax13';
                } else {
                    $tariff_key = 'mfmax13';
                }
                break;
        }
        
        if ($incadrare === 'regula20' && $quantity == 20) {
            $tariff_key = 'half';
        }
        
        $base_cost = isset($rez_transport[$tariff_key]) ? $rez_transport[$tariff_key] : 0;
        
        if ($zona_extra === 'zona1') {
            $zone_total = 0;
        } elseif ($zona_extra === 'zona2') {
            if ($nr_paleti >= 4) {
                $zone_total = 100;
            } else {
                $zone_total = 30 * $nr_paleti;
            }
        } elseif ($zona_extra === 'zona3') {
            if ($nr_paleti >= 4) {
                $zone_total = 200;
            } else {
                $zone_total = 55 * $nr_paleti;
            }
        } else {
            $zone_total = 0;
        }
        
        $transport_cost = $base_cost * $nr_paleti;
        $subtotal = $transport_cost + $zone_total;
        $total_cost = ($subtotal * $tva_rate) + $subtotal;
        
        $fee += $total_cost;
    }
    
    return $fee;
}

// Aplică fee-ul de livrare în coș.
function apply_delivery_fee( $cart ) {
    if ( empty( $cart->get_cart() ) ) {
        return;
    }
    
    $data = determine_delivery_method( $cart );
    if ( ! $data ) {
        return;
    }
    
    $method = $data['method'];
    $distance = $data['distance'];
    $fee_net = 0;
    
    if ( $method === 'ridicare' ) {
        $fee_net = 0;
    } elseif ( $method === 'transport_propriu' ) {
        $fee_net = calculate_transport_propriu_fee( $cart, $distance );
    } elseif ( $method === 'curier_sameday' ) {
        $fee_net = calculate_curier_fee( $cart );
    } elseif ( $method === 'pallex' ) {
        $post_data = get_checkout_post_data();
        $billing_state = ! empty( $post_data['billing_state'] ) ? sanitize_text_field( $post_data['billing_state'] ) : WC()->customer->get_billing_state();
        $region = get_state_name( $billing_state );
        $fee_net = calculate_pallex_fee( $cart, $distance, $region );
    }
    
    $cart->add_fee( 'Cost Transport (TVA inclus)', $fee_net, true, '' );
    WC()->session->set( 'taxe_livrare', $fee_net );
}
add_action( 'woocommerce_cart_calculate_fees', 'apply_delivery_fee', 999 );

// Filtrează shipping rates.
function filter_shipping_methods_by_selected( $rates, $package ) {
    $selected = WC()->session->get('livrare_metoda');
    if ( $selected && $selected !== 'transport_propriu' && $selected !== 'ridicare' ) {
        foreach ( $rates as $rate_id => $rate ) {
            if ( $rate->method_id !== $selected ) {
                unset( $rates[$rate_id] );
            }
        }
    }
    return $rates;
}
add_filter( 'woocommerce_package_rates', 'filter_shipping_methods_by_selected', 10, 2 );

// Afișează opțiunile de livrare în checkout.
function add_custom_shipping_options_checkout() {
    $post_data = get_checkout_post_data();
    $billing_state   = ! empty( $post_data['billing_state'] ) ? sanitize_text_field( $post_data['billing_state'] ) : WC()->customer->get_billing_state();
    $billing_address = ! empty( $post_data['billing_address_1'] ) ? sanitize_text_field( $post_data['billing_address_1'] ) : WC()->customer->get_billing_address_1();
    $municipality = ! empty( $post_data['billing_city'] ) ? sanitize_text_field( $post_data['billing_city'] ) : WC()->customer->get_billing_city();

    echo '<div id="custom_shipping_options" style="overflow-x: auto; margin-bottom: 20px; padding: 5px 25px; border-radius: var(--wd-brd-radius); background-color: var(--bgcolor-white); box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);">';
    if ( $billing_state === '' || empty($billing_address) || empty($municipality) ) {
        echo '<p style="color:red;">' . __( 'Adresa de livrare necesară pentru calculul costului transportului', 'woocommerce' ) . '</p>';
    } else {
        $data = determine_delivery_method( WC()->cart );
        $computed_method = $data['computed_method'];
        $computed_text = get_delivery_method_text( 'auto', $computed_method );
        $override = isset($post_data['override_metoda']) ? sanitize_text_field($post_data['override_metoda']) : 'auto';
        ?>
        <p><strong>📦 Alege modul de livrare:</strong></p>
        <label style="margin-right:1em;">
            <input type="radio" name="my_select_metoda" value="auto" <?php checked( $override === 'auto' ); ?> />
            <?php echo esc_html( $computed_text ); ?>
        </label>
        <label>
            <input type="radio" name="my_select_metoda" value="ridicare" <?php checked( $override === 'ridicare' ); ?> />
            <?php _e( '📍 Ridicare din depozit', 'woocommerce' ); ?>
        </label>
        <a class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-extra-small bttn-partener" href="https://maps.app.goo.gl/kJ9B9kLX4KSZbfUF8" target="_blank" rel="noopener noreferrer"> Obține locația depozitului ARDE</a>
        <input type="hidden" name="override_metoda" id="override_metoda" value="<?php echo esc_attr( $override ); ?>" />
        <?php
    }
    echo '</div>';
}
add_action( 'woocommerce_review_order_before_payment', 'add_custom_shipping_options_checkout', 10 );

function display_shipping_info_in_order_review() {
    $livrare_metoda = WC()->session->get( 'livrare_metoda' );
    $taxe_livrare  = WC()->session->get( 'taxe_livrare' );
    
    if ( empty( $livrare_metoda ) ) {
        $data = determine_delivery_method( WC()->cart );
        $livrare_metoda = $
