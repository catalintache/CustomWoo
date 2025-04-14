<?php
// includes/whatsapp-notifications.php

// Verifică dacă WooCommerce este activ
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')) ) ) {
    return;
}

if ( ! class_exists( 'TwilioWhatsAppNotifier' ) ) {
    class TwilioWhatsAppNotifier {
        private static $sid         = ''; // actualizează cu SID-ul tău
        private static $token       = '';    // actualizează cu token-ul tău
        private static $from_number = '';              // actualizează cu numărul tău WhatsApp
        private static $contentSid        = '';
        private static $contentVariables  = '{"1":"12/1","2":"3pm"}';

        public static function send_message( $to, $message ) {
            $valid_phone = self::validate_ro_phone( $to );
            if ( ! $valid_phone ) {
                error_log( "Număr invalid: $to" );
                return false;
            }
            if ( ! class_exists( 'Twilio\Rest\Client' ) ) {
                require_once get_stylesheet_directory() . '/vendor/autoload.php';
            }
            $client = new Twilio\Rest\Client( self::$sid, self::$token );
            $data = array(
                'from' => self::$from_number,
                'body' => $message,
            );
            if ( ! empty( self::$contentSid ) ) {
                $data['contentSid'] = self::$contentSid;
            }
            if ( ! empty( self::$contentVariables ) ) {
                $data['contentVariables'] = self::$contentVariables;
            }
            try {
                $result = $client->messages->create( 'whatsapp:' . $valid_phone, $data );
                return $result;
            } catch ( Exception $e ) {
                error_log( 'Twilio WhatsApp error: ' . $e->getMessage() );
                return false;
            }
        }

        public static function validate_ro_phone( $phone ) {
            $phone_clean = preg_replace( '/[^\d\+]/', '', $phone );
            if ( substr( $phone_clean, 0, 4 ) === '+407' ) {
                return $phone_clean;
            }
            $digits = preg_replace( '/\D/', '', $phone_clean );
            if ( substr( $digits, 0, 2 ) === '07' && strlen( $digits ) === 10 ) {
                return '+40' . substr( $digits, 1 );
            }
            return false;
        }
    }
}

function send_preluata_whatsapp_message( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }
    $phone = $order->get_billing_phone();
    $message = "Comanda ta urmează să fie livrată.";
    $result = TwilioWhatsAppNotifier::send_message( $phone, $message );
    if ( $result ) {
        update_post_meta( $order_id, '_whatsapp_preluare_status', 'Trimis' );
    } else {
        update_post_meta( $order_id, '_whatsapp_preluare_status', 'Eroare trimitere notificare preluare' );
    }
}
add_action( 'woocommerce_order_status_preluata', 'send_preluata_whatsapp_message', 10, 1 );

function schedule_finalizata_whatsapp_review_message( $order_id ) {
    wp_schedule_single_event( time() + 60, 'send_finalizata_whatsapp_review_message', array( $order_id ) );
}
add_action( 'woocommerce_order_status_completed', 'schedule_finalizata_whatsapp_review_message', 10, 1 );

function send_finalizata_whatsapp_review_message( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }
    $phone = $order->get_billing_phone();
    $first_link = '';
    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();
        $link = get_permalink( $product_id );
        if ( $link ) {
            $first_link = $link . '#tab-reviews';
            break;
        }
    }
    if ( empty( $first_link ) ) {
        update_post_meta( $order_id, '_whatsapp_review_status', 'Nu s-au găsit linkuri' );
        return;
    }
    $message = "Vă mulțumim pentru comanda dvs.! Vă rugăm să lăsați un review: " . $first_link;
    $result = TwilioWhatsAppNotifier::send_message( $phone, $message );
    if ( $result ) {
        update_post_meta( $order_id, '_whatsapp_review_status', 'Trimis' );
    } else {
        update_post_meta( $order_id, '_whatsapp_review_status', 'Eroare trimitere notificare review' );
    }
}
add_action( 'send_finalizata_whatsapp_review_message', 'send_finalizata_whatsapp_review_message', 10, 1 );

function display_whatsapp_preluare_status_in_order( $order ) {
    $status = get_post_meta( $order->get_id(), '_whatsapp_preluare_status', true );
    if ( ! empty( $status ) ) {
        echo '<p><strong>Notificare preluare comandă:</strong> ' . esc_html( $status ) . '</p>';
    }
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'display_whatsapp_preluare_status_in_order' );

function display_whatsapp_review_status_in_order( $order ) {
    $status = get_post_meta( $order->get_id(), '_whatsapp_review_status', true );
    if ( ! empty( $status ) ) {
        echo '<p><strong>Notificare review WhatsApp:</strong> ' . esc_html( $status ) . '</p>';
    }
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'display_whatsapp_review_status_in_order' );
