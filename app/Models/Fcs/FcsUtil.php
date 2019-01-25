<?php

namespace App\Models\Fcs;

class FcsUtil {

    public static function log($content) {
        pflog('fcs', $content);
    }

}
