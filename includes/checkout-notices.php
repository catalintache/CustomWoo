<?php
// includes/checkout-notices.php

// Afișează blocul de notificări în pagina de checkout
function arde_checkout_notices_block() {
    if ( ! is_checkout() ) return;
    
    $cart = WC()->cart;
    if ( ! $cart ) return;
    
    $total_paleti = 0;
    $has_peleti  = false;
    $has_lemne   = false;
    $distance    = WC()->session->get( 'calculated_distance' ) ?? 0;
    $computed_method = WC()->session->get( 'livrare_metoda' ) ?? '';
    
    $peleti_slugs = array( 'peleti', 'brichete-rumegus' );
    $lemne_slugs  = array( 'lemne-de-foc' );
    
    $produse_index = array();
    if ( isset( $GLOBALS['produse'] ) && is_array( $GLOBALS['produse'] ) ) {
        foreach ( $GLOBALS['produse'] as $produs ) {
            if ( isset( $produs['id'] ) ) {
                $produse_index[ $produs['id'] ] = $produs;
            }
        }
    }
    
    foreach ( $cart->get_cart() as $cart_item ) {
        $produs_id = $cart_item['product_id'];
        $quantity  = intval( $cart_item['quantity'] );
        $nr_paleti = 0;
    
        if ( isset( $produse_index[ $produs_id ] ) ) {
            $produs = $produse_index[ $produs_id ];
            $greutate_unitar = floatval( $produs['greutate_unitar'] );
            $greutate_palet  = floatval( $produs['greutate_palet'] );
            if ( $greutate_unitar <= 0 ) { $greutate_unitar = 1; }
            if ( $greutate_palet <= 0 ) { $greutate_palet = 1000; }
            $units_per_palet = $greutate_palet / $greutate_unitar;
            $nr_paleti = ceil( $quantity / $units_per_palet );
        }
        if ( $nr_paleti == 0 ) {
            $w = floatval( $cart_item['data']->get_weight() );
            if ( $w <= 0 ) { $w = 1; }
            $nr_paleti = ceil( ($w * $quantity) / 1000 );
        }
        $total_paleti += $nr_paleti;
    
        $product = $cart_item['data'];
        $product_slugs = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );
        if ( array_intersect( $peleti_slugs, $product_slugs ) ) {
            $has_peleti = true;
        }
        if ( array_intersect( $lemne_slugs, $product_slugs ) ) {
            $has_lemne = true;
        }
    }
    
    echo '<div id="arde-msg-wrapper"
        data-paleti="' . esc_attr( $total_paleti ) . '"
        data-distance="' . esc_attr( $distance ) . '"
        data-has-peleti="' . ($has_peleti ? 'yes' : 'no') . '"
        data-has-lemne="' . ($has_lemne ? 'yes' : 'no') . '"
        data-computed-method="' . esc_attr( $computed_method ) . '"
        style="display:none;">';
    echo '<div id="arde-msg-1" class="woocommerce-message arde-custom-warning" style="display:none;"><p id="arde-msg-1-text"></p><a href="' . esc_url( wc_get_cart_url() ) . '" class="button wc-forward"><i class="fa-solid fa-cart-shopping"></i> Mergi în coș</a></div>';
    echo '<div id="arde-msg-2" class="woocommerce-message arde-custom-success" style="display:none;"><p id="arde-msg-2-text"></p><a href="' . esc_url( wc_get_cart_url() ) . '" class="button wc-forward"><i class="fa-solid fa-cart-shopping"></i> Mergi în coș</a></div>';
    echo '<div id="arde-msg-3" class="woocommerce-message arde-custom-danger" style="display:none; background-color:#aa1e24; color:#fff; padding:10px;"><p id="arde-msg-3-text"></p></div>';
    echo '</div>';
}
add_action( 'woocommerce_before_checkout_form', 'arde_checkout_notices_block' );

// Scriptul pentru actualizarea dinamică a mesajelor
function arde_checkout_notice_script_block() {
    if ( ! is_checkout() ) return;
    ?>
<script>
(function(){
    function updateArdeMessages(){
        const wrapper  = document.getElementById("arde-msg-wrapper");
        const msg1     = document.getElementById("arde-msg-1");
        const msg2     = document.getElementById("arde-msg-2");
        const msg3     = document.getElementById("arde-msg-3");
        const msg1Text = document.getElementById("arde-msg-1-text");
        const msg2Text = document.getElementById("arde-msg-2-text");
        const msg3Text = document.getElementById("arde-msg-3-text");

        if (!wrapper || !msg1 || !msg2 || !msg3 || !msg1Text || !msg2Text || !msg3Text) {
            console.warn("Elementele de notificare lipsesc");
            return;
        }
        
        const distanceField = document.getElementById("calculated_distance");
        if (distanceField) {
            const newDistance = parseFloat(distanceField.value || "0");
            wrapper.dataset.distance = newDistance;
            console.log("New distance set:", newDistance);
        }
        
        const target = document.querySelector(".wd-table-wrapper.wd-manage-on");
        if (target) {
            target.insertAdjacentElement("afterend", wrapper);
        }
        
        const selectedRadio = document.querySelector('input[name="my_select_metoda"]:checked');
        let shippingMethodText = "";
        if (selectedRadio) {
            const parentLabel = selectedRadio.closest("label");
            if (parentLabel) {
                shippingMethodText = parentLabel.textContent.trim();
            }
        }
        console.log("Shipping method selectat:", shippingMethodText);
        
        if (shippingMethodText.toLowerCase().indexOf("arde") === -1) {
            wrapper.style.display = "none";
            return;
        }
        
        const distance = parseFloat(wrapper.dataset.distance || "0");
        const paleti   = parseInt(wrapper.dataset.paleti || "0", 10);
        console.log("Distance:", distance, "Paleti:", paleti);
        
        if (paleti >= 6) {
            wrapper.style.display = "block";
            msg3Text.textContent = "Pentru comenzi de peste 5 paleți, veți fi contactat pentru cost personalizat de transport.";
            msg3.style.display = "block";
            msg1.style.display = "none";
            msg2.style.display = "none";
            return;
        }
        
        if (distance < 0) {
            wrapper.style.display = "none";
            return;
        }
        wrapper.style.display = "block";
    
        if (distance >= 0 && distance < 15) {
            if (paleti === 1) {
                msg1Text.textContent = "Pentru 2 paleți până la 5 paleți, tariful de transport este fix, doar 140,42 lei.";
                msg1.style.display = "block";
                msg2.style.display = "none";
            } else if (paleti >= 2 && paleti < 5) {
                msg2Text.textContent = "Poți adăuga până la 5 paleți la același tarif de transport.";
                msg2.style.display = "block";
                msg1.style.display = "none";
            } else {
                msg1.style.display = "none";
                msg2.style.display = "none";
            }
        }
        else if (distance >= 15 && distance < 21) {
            if (paleti === 1) {
                msg1Text.textContent = "Pentru 2 paleți până la 5 paleți, tariful de transport este fix.";
                msg1.style.display = "block";
                msg2.style.display = "none";
            } else if (paleti >= 2 && paleti < 5) {
                msg2Text.textContent = "Poți adăuga până la 5 paleți la același tarif de transport.";
                msg2.style.display = "block";
                msg1.style.display = "none";
            } else {
                msg1.style.display = "none";
                msg2.style.display = "none";
            }
        }
        else if (distance >= 21 && distance < 31) {
            if (paleti === 1) {
                msg1Text.textContent = "Pentru 2 paleți până la 5 paleți, tariful de transport este fix.";
                msg1.style.display = "block";
                msg2.style.display = "none";
            } else if (paleti >= 2 && paleti < 5) {
                msg2Text.textContent = "Poți adăuga până la 5 paleți la același tarif de transport.";
                msg2.style.display = "block";
                msg1.style.display = "none";
            } else {
                msg1.style.display = "none";
                msg2.style.display = "none";
            }
        }
        else if (distance >= 31 && distance < 41) {
            if (paleti === 1) {
                msg1Text.textContent = "Pentru 2 paleți până la 5 paleți, tariful de transport este fix.";
                msg1.style.display = "block";
                msg2.style.display = "none";
            } else if (paleti >= 2 && paleti < 5) {
                msg2Text.textContent = "Poți adăuga până la 5 paleți la același tarif de transport.";
                msg2.style.display = "block";
                msg1.style.display = "none";
            } else {
                msg1.style.display = "none";
                msg2.style.display = "none";
            }
        }
        else if (distance >= 41 && distance < 51) {
            if (paleti === 1) {
                msg1Text.textContent = "Adaugă încă 1 palet la același tarif de transport.";
                msg1.style.display = "block";
                msg2.style.display = "none";
            } else if (paleti >= 2 && paleti < 5) {
                msg2Text.textContent = "Poți adăuga până la 5 paleți la același tarif de transport.";
                msg2.style.display = "block";
                msg1.style.display = "none";
            } else {
                msg1.style.display = "none";
                msg2.style.display = "none";
            }
        }
        else if (distance >= 51 && distance < 61) {
            if (paleti === 1) {
                msg1Text.textContent = "Adaugă încă 1 palet la același tarif de transport.";
                msg1.style.display = "block";
                msg2.style.display = "none";
            } else if (paleti >= 2 && paleti < 5) {
                msg2Text.textContent = "Poți adăuga până la 5 paleți la același tarif de transport.";
                msg2.style.display = "block";
                msg1.style.display = "none";
            } else {
                msg1.style.display = "none";
                msg2.style.display = "none";
            }
        }
        else if (distance >= 61 && distance <= 100) {
            if (paleti === 1) {
                msg1Text.textContent = "Adaugă încă 1 palet la același tarif de transport.";
                msg1.style.display = "block";
                msg2.style.display = "none";
            } else if (paleti >= 2 && paleti < 5) {
                msg2Text.textContent = "Poți adăuga până la 5 paleți la același tarif de transport.";
                msg2.style.display = "block";
                msg1.style.display = "none";
            } else {
                msg1.style.display = "none";
                msg2.style.display = "none";
            }
        }
        else {
            msg1.style.display = "none";
            msg2.style.display = "none";
        }
    }
    
    document.querySelectorAll('input[name="my_select_metoda"]').forEach(radio => {
        radio.addEventListener("change", function(){
            setTimeout(updateArdeMessages, 100);
        });
    });
    
    const override = document.getElementById("override_metoda");
    if (override) {
        const observer = new MutationObserver(() => {
            setTimeout(updateArdeMessages, 100);
        });
        observer.observe(override, { attributes: true, attributeFilter: ["value"] });
    }
    
    jQuery(document.body).on("updated_checkout", function(){
        setTimeout(updateArdeMessages, 150);
    });
    
    jQuery(function($){
        const fields = ['#billing_address_1', '#billing_state', '#billing_city'];
        fields.forEach(selector => {
            $(document.body).on("change blur", selector, function(){
                setTimeout(function(){
                    $(document.body).trigger("update_checkout");
                }, 300);
            });
        });
    });
    
    setTimeout(updateArdeMessages, 300);
})();
</script>
<?php
}
add_action( 'wp_footer', 'arde_checkout_notice_script_block', 30 );

// Salvează în meta comanda notificarea pentru shipping dacă numărul de paleți este cel puțin 6
function save_shipping_over6_notice_order_meta( $order_id ) {
    $cart = WC()->cart;
    if( ! $cart ) return;
    
    $total_paleti = 0;
    foreach ( $cart->get_cart() as $cart_item ) {
        $quantity = intval( $cart_item['quantity'] );
        $nr_paleti = 0;
        if ( isset( $GLOBALS['produse'] ) && is_array( $GLOBALS['produse'] ) ) {
            foreach ( $GLOBALS['produse'] as $produs ) {
                if ( $produs['id'] == $cart_item['product_id'] ) {
                    $greutate_unitar = floatval( $produs['greutate_unitar'] );
                    $greutate_palet  = floatval( $produs['greutate_palet'] );
                    if ( $greutate_unitar <= 0 ) { $greutate_unitar = 1; }
                    if ( $greutate_palet <= 0 ) { $greutate_palet = 1000; }
                    $units_per_palet = $greutate_palet / $greutate_unitar;
                    $nr_paleti = ceil( $quantity / $units_per_palet );
                    break;
                }
            }
        }
        if ( $nr_paleti == 0 ) {
            $w = floatval( $cart_item['data']->get_weight() );
            if ( $w <= 0 ) { $w = 1; }
            $nr_paleti = ceil( ($w * $quantity) / 1000 );
        }
        $total_paleti += $nr_paleti;
    }
    
    if ( $total_paleti >= 6 ) {
        update_post_meta( $order_id, '_shipping_over6_notice', 'Pentru comenzi de peste 5 paleți, veți fi contactat pentru cost personalizat de transport.' );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'save_shipping_over6_notice_order_meta' );

// Afișează notificarea în emailurile de comandă
function display_shipping_over6_notice_in_email( $order, $sent_to_admin, $plain_text, $email ) {
    $notice = get_post_meta( $order->get_id(), '_shipping_over6_notice', true );
    if ( $notice ) {
        if ( $plain_text ) {
            echo "\n" . $notice . "\n";
        } else {
            echo '<p style="background:#aa1e24; color:white; font-weight:bold; padding:10px;">' . esc_html( $notice ) . '</p>';
        }
    }
}
add_action( 'woocommerce_email_after_order_table', 'display_shipping_over6_notice_in_email', 10, 4 );

// Adaugă notificarea în pagina de finalizare (thank you)
function custom_append_shipping_notice_to_thankyou( $thankyou_text, $order ) {
    $notice = get_post_meta( $order->get_id(), '_shipping_over6_notice', true );
    if ( $notice ) {
        $custom = '<div class="woocommerce-notice woocommerce-notice--success" style="background:#aa1e24;color:#fff;font-weight:bold;padding:10px;margin-top:15px;display:flex;justify-content:center;">' . esc_html( $notice ) . '</div>';
        return $thankyou_text . $custom;
    }
    return $thankyou_text;
}
add_filter( 'woocommerce_thankyou_order_received_text', 'custom_append_shipping_notice_to_thankyou', 10, 2 );

// Actualizează fragmentele de checkout pentru notificări
function update_custom_checkout_fragment( $fragments ) {
    ob_start();
    arde_checkout_notices_block();
    $html = ob_get_clean();
    $fragments['div#arde-msg-wrapper'] = $html;
    return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'update_custom_checkout_fragment', 10, 1 );
