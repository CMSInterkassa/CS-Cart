<?php
/**
 * Модуль оплаты Интеркасса
 * Разработка модуля GateOn
 * www.gateon.net
 * www@smartbyte.pro
 * Версия 1.2 2017
 */

//error_reporting(E_ALL);ini_set("display_errors", 0);

use Tygh\Http;
use Tygh\Registry;
if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = !empty($_REQUEST['ik_pm_no']) ? $_REQUEST['ik_pm_no'] : 0;

    if ($mode == 'notify') {
        if(!checkIP()) exit();
            $pp_response = array(
                'order_status' => 'P'
            );

            $pp_response["transaction_id"] = $_REQUEST['ik_inv_id'];

            if (fn_check_payment_script('interkassa.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }

            $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
            $processor_data = fn_get_payment_method_data($payment_id);

            $merchant_id = $processor_data['processor_params']['merchant_id'];

            if(isset($processor_data['processor_params']['test_mode'])){
                $key = $processor_data['processor_params']['test_key'];
            } else {
                $key = $processor_data['processor_params']['sekret_key'];
            }

            $data = array();
            foreach ($_REQUEST as $key => $value) {
                if (!preg_match('/ik_/', $key)) continue;
                $data[$key] = $value;
            }


            $ik_sign = $data['ik_sign'];
            var_dump($_REQUEST);

            unset($data['ik_sign']);
            ksort($data, SORT_STRING);

            array_push($data, $key);
            $signString = implode(':', $data);
            $sign = base64_encode(md5($signString, true));

            if ($sign === $ik_sign && $data['ik_co_id'] === $merchant_id) {
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

                fn_order_placement_routines('route', $order_id);
                //header("Location: index.php?dispatch=checkout.complete&order=$order_id");
                //exit;

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

	if (isset($processor_data['processor_params']['test_mode'])){
		$secret_key = $processor_data['processor_params']['test_key'];
	} else {
		$secret_key = $processor_data['processor_params']['sekret_key'];
	}


    $order_desc = "#" . $order_info['order_id'];

	$htprot = 'http://';
	if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443){
		$htprot = 'https://';
	}

	$site = $htprot . $_SERVER['SERVER_NAME'];

    $post_data = array(
      'ik_am' => fn_paymaster_get_sum($order_info, $processor_data),
      'ik_cur' => $processor_data['processor_params']['currency'],
      'ik_co_id' => $processor_data['processor_params']['merchant_id'],
      'ik_pm_no' => $order_info['order_id'],
      'ik_desc' => $order_desc,
		  'ik_ia_u' => $site . "/?dispatch=payment_notification.notify&payment=interkassa",
		  'ik_suc_u' => $site . "/?dispatch=payment_notification.success&payment=interkassa",
      'ik_fal_u' => $site . "/?dispatch=payment_notification.error&payment=interkassa",
      'ik_pnd_u' => $site . "/?dispatch=payment_notification.return&payment=interkassa"
    );
    // $qwer = $post_data;
    // ksort($qwer, SORT_STRING);
    // array_push($qwer, $secret_key);
    // var_dump($qwer);
    // $signString = implode(':', $qwer);
    // $sign = base64_encode(md5($signString, true));
    //
    // ksort($post_data, SORT_STRING);
    //
    // array_push($dataSet, $secret_key);
    // $signString = implode(':', $post_data);
    //
    // $signature = base64_encode(md5($signString, true));
    // // unset($post_data["ik_sign"]);

    // var_dump($_SESSION['ik_love_web']);
    $post_data["ik_sign"] = SignCraft($post_data,$secret_key);
    $ik_dir = '/app/payments/interkassa_files/';
    include 'interkassa_files/tmp.php';

    // $fd = fopen(__DIR__."/interkassa_files/s.php", 'w') or die("Обновите страницу.");
    // fwrite($fd,"<?php");
    // fseek($fd, 1, SEEK_END); // поместим указатель в конец
    // // fwrite($fd,); // запишем в конце еще одну строку
    // file_put_contents($fd,'$feel="'.$secret_key.'";');
    // fclose($fd);

    $file = __DIR__."/interkassa_files/s.php";
    // Открываем файл для получения существующего содержимого
    // $current = file_get_contents($file);
    // Добавляем нового человека в файл
    // $current .= "John Smith\n";
    // Пишем содержимое обратно в файл
    file_put_contents($file,'<?php
    $feel="'.$secret_key.'";');

// var_dump($post_data);
// die();
    // fn_create_payment_form($post_address, $post_data, 'Interkassa', false);
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

function checkIP(){
    $ip_stack = array(
        'ip_begin'=>'151.80.190.97',
        'ip_end'=>'151.80.190.104'
    );

    if(!ip2long($_SERVER['REMOTE_ADDR'])>=ip2long($ip_stack['ip_begin']) && !ip2long($_SERVER['REMOTE_ADDR'])<=ip2long($ip_stack['ip_end'])){
        exit();
    }
    return true;
}
function SignCraft($data,$secret_key)
{
  if(!empty($data['ik_sign'])) unset($data['ik_sign']);
  $dataSet = array();
  foreach ($data as $key=>$value) {
    if(!preg_match('/ik_/', $key))continue;
    $dataSet[$key]=$value;
  }

  ksort($dataSet,SORT_STRING);
  array_push($dataSet,$secret_key);
  $arg = implode(':',$dataSet);
  $sign = base64_encode(md5($arg, true));

  return $sign;
}
function getIkPaymentSystems($uid,$aid,$ake)
{
  $json_data = json_decode(file_get_contents("https://api.interkassa.com/v1/paysystem-input-payway?checkoutId=$uid",false,stream_context_create(array('http'=>array('method'=>"GET",'header'=>"Authorization: Basic ".base64_encode("$aid:$ake"))))));
  if($json_data->status != 'error'){

    $payment_systems = array();
    foreach ($json_data->data as $ps => $info) {
      $payment_system = $info->ser;
      if (!array_key_exists($payment_system, $payment_systems)) {
        $payment_systems[$payment_system] = array();
        foreach ($info->name as $name) {
          if ($name->l == 'en') $payment_systems[$payment_system]['title'] = ucfirst($name->v);
          $payment_systems[$payment_system]['name'][$name->l] = $name->v;
        }
      }
      $payment_systems[$payment_system]['currency'][strtoupper($info->curAls)] = $info->als;
    }
    return $payment_systems;
  }else echo '<strong style="color:red;">API connection error!<br>'.$json_data->message.'</strong>';
}
exit;
