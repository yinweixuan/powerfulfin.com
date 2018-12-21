<?php
/**
 * Created by PhpStorm.
 * User: 淘气
 * Date: 2017/02/04
 * Time: 23:32
 */
header("Content-type: text/html; charset=utf-8");
//====================配置商户的宝付接口授权参数============================
$path = $_SERVER['DOCUMENT_ROOT']."/Verify-BankCard/";
$rsapath = $path."library/rsa/";	//证书路径
require_once($path."library/utils/HttpCurl.php");
require_once($path."library/utils/BaofooUtils.php");
require_once($path."library/utils/Log.php");
require_once($path."library/utils/BFRSA.php");
require_once($path."library/utils/HttpClient.php");

//默认编码格式//
$char_set="UTF-8";
//商户私钥   ---请修改为自己的//
$pfxpath=$rsapath."8000013189_pri.pfx";
//商户私钥密码 ---请修改为自己的//
$pfx_pwd="217526";
//公钥 ---请修改为自己的//
$cerpath=$rsapath."bfkey_8000013189.cer";
//终端号 ---请修改为自己的//
$terminal_id="8000013189";
//商户号 ---请修改为自己的//
$member_id="8000013189";
//数据类型////json/xml
$data_type="json";

//======2.2.1 银行卡认证(三要素、四要素)======
//测试地址
$bankCardAuthUrl="http://test.xinyan.com/bankcard/v3/auth";


//======2.2.2 银行卡四要素验证短信申请======
//测试地址
$authApplyUrl="http://test.xinyan.com/bankcard/v1/authsms";

//======2.2.3银行卡四要素验证确认======
//测试地址
$authConfirmUrl="http://test.xinyan.com/bankcard/v1/authconfirm";



//======卡bin======
#测试地址
$bankCardBinUrl="http://test.xinyan.com/product/bankcard/v1/bin/info";











