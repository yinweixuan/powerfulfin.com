<?php
/**
 * Company: www.baofu.com
 * @author dasheng(大圣)
 * @date 2018年3月15日
 */
 class BFRSAUtil{    
    /**
     * 读取私钥
     * @param type $PfxPath
     * @param type $PrivateKPASS
     * @return array
     * @throws Exception
     */
    private static function  getPriveKey($PfxPath,$PrivateKPASS){
        try { 
            if(!file_exists($PfxPath)) {
                throw new Exception("私钥文件不存在！路径：".$PfxPath);
            }
            $PKCS12 = file_get_contents($PfxPath);
            $PrivateKey = array();
            if(openssl_pkcs12_read($PKCS12, $PrivateKey, $PrivateKPASS)){
                return $PrivateKey["pkey"];
             }else{
                throw new Exception("私钥证书读取出错！原因[证书或密码不匹配]，请检查本地证书相关信息。");
             }
         } catch (Exception $ex) {
            $ex->getTrace();
        }
    }
    
    /**
     * 读取公钥
     * @param type $PublicPath
     * @return type
     * @throws Exception
     */
    private static function  getPublicKey($PublicPath){
        try { 
            if(!file_exists($PublicPath)) {
                throw new Exception("公钥文件不存在！路径：".$PublicPath);
            }      
            $KeyFile = file_get_contents($PublicPath);
            $PublicKey = openssl_get_publickey($KeyFile); 
            return $PublicKey;
         } catch (Exception $ex) {
            $ex->getTraceAsString();
        }
    }


    /**
     * 私钥加密码
     * @param type $Data    加密数据
     * @param type $PfxPath     私钥路径
     * @param type $PrivateKPASS  私钥密码
     * @return type
     * @throws Exception
     */
    public static function encryptByCERFile($Data,$PublicPath){
        try {
            if (!function_exists( 'bin2hex')) {
                throw new Exception("bin2hex PHP5.4及以上版本支持此函数，也可自行实现！");
            }
            $KeyObj = self::getPublicKey($PublicPath);            
            $BASE64EN_DATA = base64_encode($Data);
            $EncryptStr = "";
            $blockSize=117;//分段长度
            $totalLen = strlen($BASE64EN_DATA);
            $EncryptSubStarLen = 0;
            $EncryptTempData="";
            while ($EncryptSubStarLen < $totalLen){
                openssl_public_encrypt(substr($BASE64EN_DATA, $EncryptSubStarLen, $blockSize), $EncryptTempData, $KeyObj);
                $EncryptStr .= bin2hex($EncryptTempData);
                $EncryptSubStarLen += $blockSize;
            }
            return $EncryptStr;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
	
    
    /**
     * 公钥解密
     * @param type $Data    解密数据
     * @param type $PublicPath  解密公钥路径    
     * @return type
     * @throws Exception
     */
    public static function decryptByPFXFile($Data,$PfxPath,$PrivateKPASS){
        try {            
            if (!function_exists( 'hex2bin')) {
                throw new Exception("hex2bin PHP5.4及以上版本支持此函数，也可自行实现！");
            }
            $KeyObj = self::getPriveKey($PfxPath,$PrivateKPASS);
            $DecryptRsult="";
            $blockSize=256;//分段长度
            $totalLen = strlen($Data);
            $EncryptSubStarLen = 0;
            $DecryptTempData="";
            while ($EncryptSubStarLen < $totalLen) {
                openssl_private_decrypt(hex2bin(substr($Data, $EncryptSubStarLen, $blockSize)), $DecryptTempData, $KeyObj);
                $DecryptRsult .= $DecryptTempData;
                $EncryptSubStarLen += $blockSize;
            }
            return base64_decode($DecryptRsult);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
}