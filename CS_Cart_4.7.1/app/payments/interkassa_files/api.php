<?php
if($_SERVER['REQUEST_METHOD']!='POST') die();
include_once __DIR__.'/s.php';
function SignCraft($data,$s)
{
  if(!empty($data['ik_sign'])) unset($data['ik_sign']);
  $dataSet = array();
  foreach ($data as $key=>$value) {
    if(!preg_match('/ik_/', $key))continue;
    $dataSet[$key]=$value;
  }

  ksort($dataSet,SORT_STRING);
  array_push($dataSet,$s);
  $arg = implode(':',$dataSet);
  $sign = base64_encode(md5($arg, true));

  return $sign;
}
function FormGet($data)
{
  $ch = curl_init('https://sci.interkassa.com/');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}
switch ($_GET['nYg']) {
  case 'nYs':
    echo json_encode(array('sign'=>SignCraft($_POST,$feel),'s'=>$feel));
    break;
  case 'nYa':
    $_POST['ik_sign']=SignCraft($_POST,$feel);
    echo json_encode(FormGet($_POST));
    break;
  default:
    die();
}
