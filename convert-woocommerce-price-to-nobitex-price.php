<?php
/*
Plugin Name: Convert Woocommerce price to Nobitex prices as Crypto
Plugin URI:
Description:
Version:
Author:
Author URI:
License:
License URI:
*/

require_once "admin.php";


function get_data_from_nobitex($amount)
{
    $url = "https://api.nobitex.ir/pg/exchange-amount/?amount=" . $amount."&currencies=";
    $currency = 'btc';

    if (get_option('xrp_rls') == 'on') {
        $currency = $currency.',ltc';
    }

    if (get_option('ltc_rls') == 'on') {
        $currency = $currency.',xrp';
    }
    $get_url = $url . $currency;

    $args = array(
        'method' => 'GET',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Content-type: application/json'
        ),
        'cookies' => array()
    );
        $ress = wp_remote_get($get_url, $args);
        $body = wp_remote_retrieve_body($ress);
        $body_array = $array = json_decode($body, true);
    $src_currency=[];
    if ($body_array['status'] == 'success') {
            foreach ($body_array['res'] as $key=>$value)
            {
                $src_currency[$key] = $value;
            }
        }
    return $src_currency;
}

function produce_new_data_by_currency_amount($best_sells)
{
    $currency = get_woocommerce_currency();

    if ($currency != 'IRT' and $currency != 'IRR') {
        return '';
    }


    $my_prices = [];
    foreach ($best_sells as $key => $value) {
        if ($currency === "IRT") {
            $converted = $value *10;
        } elseif ($currency === "IRR") {
            $converted = $value;
        }
        $my_prices[$key] = sprintf('%.8f', floatval($converted));
    }
    $data='';
    foreach ($my_prices as $key => $value) {
        $data = $data. '<br>' . $value . ' ' . $key;
    }
    return $data;
}


$convert_pair_key_to_value = ['btc-rls' => 'btc', 'ltc-rls' => 'ltc', 'xrp-rls' => 'xrp'];

add_filter('woocommerce_get_price_html', 'convert_price_product', 10, 2);

function convert_price_product($data, $args)
{
    global $convert_pair_key_to_value;



    $best_sells = get_data_from_nobitex($args->price);
    $my_data = produce_new_data_by_currency_amount($best_sells);

    return $data . $my_data;
}

function my_variation($data, $product, $variation)
{
    $best_sells = get_data_from_nobitex($data['display_price']);
    $my_data = produce_new_data_by_currency_amount($best_sells);

    return $data . $my_data;
}

add_filter('woocommerce_cart_item_price', 'card_item_price_correction', 10, 3);
//add_filter('woocommerce_variation_price', 'my_variation2', 10, 3);

function card_item_price_correction($price, $cart_item, $cart_item_key)
{
    $currency = get_woocommerce_currency();
    if ($currency != 'IRT' and $currency != 'IRR') {
        return $price;
    }

    $variation_id = ($cart_item['variation_id'] != 0) ? $cart_item['variation_id'] : $cart_item['product_id'];
    $variable_product = wc_get_product($variation_id);
    $variation_price = $variable_product->get_price();

    $best_sells = get_data_from_nobitex($variation_price);

    $my_data = produce_new_data_by_currency_amount($best_sells);

    return $price . $my_data;
}


add_filter('woocommerce_cart_item_subtotal', 'card_item_subtotal_correction', 10, 3); // added

function card_item_subtotal_correction($price, $cart_item, $cart_item_key)
{
    $subtotal_price = $cart_item['line_total'];
    $best_sells = get_data_from_nobitex( $subtotal_price);

    $my_data = produce_new_data_by_currency_amount($best_sells);
    return $price . $my_data;
}

add_filter('woocommerce_cart_subtotal', 'cart_subtotal_correction', 10, 3); // added
function cart_subtotal_correction($price, $cart_item, $cart_item_key)
{
    global $convert_pair_key_to_value;

    $currency = get_woocommerce_currency();
    if ($currency != 'IRT' and $currency != 'IRR') {
        return $price;
    }

    $subtotal_price = $cart_item_key->subtotal;
    $best_sells = get_data_from_nobitex($subtotal_price);


    $my_data = produce_new_data_by_currency_amount($best_sells);
    return $price . $my_data;
}

add_filter('woocommerce_cart_totals_order_total_html', 'cart_total_correction', 10); // added
function cart_total_correction($price)
{
    global $woocommerce;

    $total_price = $woocommerce->cart->total;
    $best_sells = get_data_from_nobitex($total_price);

    $my_data = produce_new_data_by_currency_amount($best_sells);
    return $price . $my_data;
}

//add_action('woocommerce_checkout_order_processed', 'change_total_on_checking', 20, 1);


add_action('woocommerce_receipt_nobitex', 'change_total_on_checking', 10, 1);
function change_total_on_checking($order_id)
{
    $currency = get_woocommerce_currency();
    if ($currency != 'IRT' and $currency != 'IRR') {
        return;
    }
    global $convert_pair_key_to_value;
    $order = wc_get_order($order_id);
    $total_price = $order->total;
    $best_sells = get_data_from_nobitex($total_price);


    foreach ($best_sells as $key => $value) {
        if ($currency === "IRT") {
            $converted = $value *10;
        } elseif ($currency === "IRR") {
            $converted = $value ;
        }
        $my_prices[$key] = sprintf('%.8f', floatval($converted));
    }
    foreach ($my_prices as $key => $value) {
        echo "<li>قیمت نهایی به " . $key . ' :' . $value . "</li><br>";
    }
}
