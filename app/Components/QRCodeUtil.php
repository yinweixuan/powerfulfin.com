<?php

namespace App\Components;

require_once __DIR__ . '/../Libraries/qrcode/QRCode.php';

/**
 * 二维码相关操作
 */
class QRCodeUtil {

    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint = false) {
        \QRcode::png($text, $outfile, $level, $size, $margin, $saveandprint);
    }

}
