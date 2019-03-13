<?php
return [
    'ios' => [
        'version' => '3.1.3',
        'force_update' => '1',
        'title' => '升级提示：3.1.3',
        'content' => "1.修复bug\n2.优化用户体验\n3.增加了新的功能",
        'url' => 'itms-services://?action=download-manifest&url=https://app.powerfulfin.com/web/app/manifest.plist'
    ],
    'android' => [
        'version' => '3.1.2',
        'force_update' => '1',
        'title' => '升级提示：3.1.2',
        'content' => "1.修复bug\n2.优化用户体验\n3.增加了新的功能",
        'url' => 'https://app.powerfulfin.com/downloadpackage?_f=update'
    ]
];