<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:53 PM
 */

namespace App\Http\Controllers\Inner;


use App\Components\PFException;
use App\Http\Controllers\Controller;
use App\Models\Server\BU\BUUdcredit;
use App\Models\Server\Udcredit\UdcreditNotify;
use Illuminate\Support\Facades\Log;

class UdcreditController extends Controller
{
    /**
     * 有盾云慧眼回调数据
     */
    public function notify()
    {
        try {
            $methodType = strtolower($_SERVER['REQUEST_METHOD']);
            Log::info("UdcreditNotify_REQUEST_METHOD: \n" . $methodType);
            if ($methodType != 'post') {
                throw new PFException("请求方式异常");
            }
            $post_data = file_get_contents('php://input');
            // 验证数据和签名
            Log::info("UdcreditNotify_Before: \n" . $post_data);
            $params = json_decode($post_data, true);
            if (!is_array($params)) {
                throw new PFException('数据格式异常');
            }
            $data = UdcreditNotify::Init($params);
            // 记录微信通知日志
            Log::info("UdcreditNotify_Afert: \n" . var_export($data, true));

            // 验证支付结果处理逻辑
            $result = BUUdcredit::NotifyHandle($data);
            if ($result) {
                UdcreditNotify::ReplyNotify(true);
            } else {
                UdcreditNotify::ReplyNotify(false);
            }
        } catch (PFException $e) {
            $respData = array('code' => '0', 'message' => $e->getMessage());
            echo json_encode($respData);
        }
    }
}
