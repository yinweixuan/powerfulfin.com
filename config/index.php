<?php

/**
 * 首页配置
 */
return [
    //顶部banner
    'banner' => [
        [
            'img' => 'https://www.powerfulfin.com/img/banner/banner1.png',
            'url' => '',
        ],
        [
            'img' => 'https://www.powerfulfin.com/img/banner/banner2.png',
            'url' => '',
        ],
    ],
    //客服信息
    'customer_service' => [
        'phone' => '4000029691',
        'email' => 'jinrongfuwu@kezhanwang.cn'
    ],
    //通知信息，初版先配置
    'notice' => [
        'content' => '',
        'url' => '',
    ],
    //IOS正在审核的版本号(满足一个就是审核）
    'ios_audit_version' => '0.0.0',
    'ios_audit_bundle_identifier' => '1.0.1.1',
];
