<?php
/**
 * Модуль оплаты Интеркасса
 * Разработка модуля GateOn
 * www.gateon.net
 * www@smartbyte.pro
 * Версия 1.2 2016
 */

//error_reporting(E_ALL);ini_set("display_errors", 0);

use Tygh\Http;
use Tygh\Registry;
if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = !empty($_REQUEST['ik_pm_no']) ? $_REQUEST['ik_pm_no'] : 0;

    if ($mode == 'notify') {
            $pp_response = array(
                'order_status' => 'P'
            );

            $pp_response["transaction_id"] = $_REQUEST['ik_inv_id'];

            if (fn_check_payment_script('interkassa.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }

            $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
            $processor_data = fn_get_payment_method_data($payment_id);

            $ik_key = $processor_data['processor_params']['sekret_key'];
            $merchant_id = $processor_data['processor_params']['merchant_id'];

            $data = array();
            foreach ($_REQUEST as $key => $value) {
                if (!preg_match('/ik_/', $key)) continue;
                $data[$key] = $value;
            }


            $ik_sign = $data['ik_sign'];
            var_dump($_REQUEST);

            unset($data['ik_sign']);
            ksort($data, SORT_STRING);

            array_push($data, $ik_key);
            $signString = implode(':', $data);
            $sign = base64_encode(md5($signString, true));

            if ($sign === $ik_sign || $data['ik_co_id'] === $merchant_id) {
                $pp_response = array(
                    'order_status' => 'P'
                );

                $pp_response["transaction_id"] = $_REQUEST['ik_inv_id'];

                if (fn_check_payment_script('interkassa.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                }

            } else {
                $order_id = $_REQUEST['ik_pm_no'];

                $pp_response['order_status'] = 'N';
                $pp_response["reason_text"] = __('text_transaction_cancelled');

                if (fn_check_payment_script('interkassa.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response, false);
                }
            }

    } elseif ($mode == 'success') {         

            $pp_response["transaction_id"] = $_REQUEST['ik_inv_id'];

            if (fn_check_payment_script('interkassa.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }

            $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
            $processor_data = fn_get_payment_method_data($payment_id);

            $merchant_id = $processor_data['processor_params']['merchant_id'];

            $data = array();
            foreach ($_REQUEST as $key => $value) {
                if (!preg_match('/ik_/', $key)) continue;
                $data[$key] = $value;
            }

            if ($data['ik_co_id'] === $merchant_id && $data['ik_inv_st'] == "success") {
                $pp_response = array(
                    'order_status' => 'P'
                );

                $pp_response["transaction_id"] = $_REQUEST['ik_inv_id'];

                if (fn_check_payment_script('interkassa.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                }

                header("Location: index.php?dispatch=checkout.complete&order=$order_id");
                exit;

            } else {
                $order_id = $_REQUEST['ik_pm_no'];

                $pp_response['order_status'] = 'N';
                $pp_response["reason_text"] = __('text_transaction_cancelled');

                if (fn_check_payment_script('interkassa.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response, false);
                }
            }

    } elseif ($mode == 'return') {

        if (fn_check_payment_script('interkassa.php', $order_id)) {

            $times = 0;
            while ($times <= PAYMASTER_MAX_AWAITING_TIME) {

                $_order_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);
                if (empty($_order_id)) {
                    break;
                }

                sleep(1);
                $times++;
            }

            $order_status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $order_id);

            if ($order_status == STATUS_INCOMPLETED_ORDER) {
                fn_change_order_status($order_id, 'O');
            }

            fn_order_placement_routines('route', $order_id, false);
        }

    } elseif ($mode == 'invoice') {

        echo "YES";

    } elseif ($mode == 'error') {

        $pp_response['order_status'] = 'N';
        $pp_response["reason_text"] = __('text_transaction_cancelled');

        if (fn_check_payment_script('interkassa.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    }

} else {

    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $post_address = "https://sci.interkassa.com/";

    $payment_desc = '';
    if (is_array($order_info['products'])) {
        foreach ($order_info['products'] as $k => $v) {
            $payment_desc .= $order_info['products'][$k]['product'] . ' / ';
        }
    }

    $payment_desc = base64_encode ($payment_desc);
   
    if (empty($processor_data['processor_params']['currency'])) {
        $processor_data['processor_params']['currency'] = 'RUB';
    }

    $secret_key = $processor_data['processor_params']['sekret_key'];

    $order_desc = "#" . $order_info['order_id'];

    $post_data = array(
        'ik_am' => fn_paymaster_get_sum($order_info, $processor_data),
        'ik_cur' => $processor_data['processor_params']['currency'],
        'ik_co_id' => $processor_data['processor_params']['merchant_id'],
        'ik_pm_no' => $order_info['order_id'],
        'ik_desc' => $order_desc,
        'ik_exp' => date("Y-m-d H:i:s", time() + 24 * 3600),
    );

    ksort($post_data, SORT_STRING);
    $post_data['secret'] = $secret_key;
    
    $signString = implode(':', $post_data);
   
    $signature = base64_encode(md5($signString, true));
    unset($post_data["secret"]);
    $post_data["ik_sign"] = $signature;

    fn_create_payment_form($post_address, $post_data, 'Interkassa', false);
}

function fn_paymaster_get_sum($order_info, $processor_data)
{
    $price = $order_info['total'];

    if (CART_PRIMARY_CURRENCY != $processor_data['processor_params']['currency']) {
        $currencies = Registry::get('currencies');
        $currency = $currencies[$processor_data['processor_params']['currency']];
        $price = fn_format_rate_value($price, 'F', $currency['decimals'], '.', '', $currency['coefficient']);
    }

    return sprintf('%.2f', $price);
}

exit;
