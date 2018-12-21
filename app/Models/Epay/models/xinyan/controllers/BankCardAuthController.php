<?php
/**
 * Created by PhpStorm.
 * User: 淘气
 * Date: 2016/10/12
 * Time: 23:50
 */
require_once("/../configs/app.php");
$acc_no=$_POST["acc_no"];//银行卡号
$id_card=$_POST["id_card"];//身份证号
$id_holder=$_POST["id_holder"];//持卡人姓名
$mobile=$_POST["mobile"];//银行预留手机号
$card_type=$_POST["card_type"];//卡类型
$verify_element=$_POST["verify_element"];//验卡类型
$valid_date_year=$_POST["valid_date_year"];//卡有效期年
$valid_date_month=$_POST["valid_date_month"];//卡有效期月
$valid_no=$_POST["valid_no"];//cvv2码
//  **组装参数(15)**
$trans_id=BaofooUtils::create_uuid();//商户订单号
$trade_date=BaofooUtils::trade_date();//交易时间
$arrayData=array(
    "member_id"=>$member_id,
    "terminal_id"=>$terminal_id,
    "id_card"=>$id_card,
    "id_holder"=>$id_holder,
    "acc_no"=>$acc_no,
    "mobile"=>$mobile,
    "card_type"=>$card_type,
    "valid_date_year"=>$valid_date_year,
    "valid_date_month"=>$valid_date_month,
    "valid_no"=>$valid_no,
    "verify_element"=>$verify_element,
    "trans_id"=>$trans_id,
    "trade_date"=>$trade_date,
    "industry_type"=>"A1",//根据自己的行业类型传入
    "product_type"=>0
);
// *** 数据格式化***
$data_content="";
//==================转换数据类型=============================================
if($data_type == "json"){
    $data_content = str_replace("\\/", "/",json_encode($arrayData));//转JSON
}

Log::LogWirte("====请求明文：".$data_content);
if (!file_exists($pfxpath)) { //检查文件是否存在
    Log::LogWirte("=====私钥不存在");
    exit;
}
if (!file_exists($cerpath)) { //检查文件是否存在
    Log::LogWirte("=====公钥不存在");
    exit;
}
Log::LogWirte($pfxpath." ".$cerpath." ".$pfx_pwd);
// **** 先BASE64进行编码再RSA加密 ***
$BFRsa = new BFRSA($pfxpath, $cerpath, $pfx_pwd,TRUE); //实例化加密类。
$data_content = $BFRsa->encryptedByPrivateKey($data_content);
Log::LogWirte("====加密串".$data_content);
/**============== http 请求==================== **/
$request_url=$bankCardAuthUrl;
$PostArry = array(
    "member_id" =>$member_id,
    "terminal_id" => $terminal_id,
    "data_type" => $data_type,
    "data_content" => $data_content);

$return = HttpClient::Post($PostArry, $request_url);  //发送请求到服务器，并输出返回结果。
Log::LogWirte("请求返回参数：".$return);
if(empty($return)){
    throw new Exception("返回为空，确认是否网络原因！");
}
//** 处理返回的报文 */
Log::LogWirte("结果：".$return);
header("Location:../statics/show.php?resultMsg=".$return);








