<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:17 PM
 */
define('PATH_BASE', dirname(__FILE__) . '/..');
define('PATH_CONFIG', PATH_BASE . '/config');
define('PATH_VENDOR', PATH_BASE . '/vendor');
define('PATH_BOOTSTRAP', PATH_BASE . '/bootstrap');
define('PATH_APP', PATH_BASE . '/app');
define('PATH_DATABASE', PATH_BASE . '/database');
define('PATH_PUBLIC', PATH_BASE . '/public');
define('PATH_RESOURCES', PATH_BASE . '/resources');
define('PATH_ROUTES', PATH_BASE . '/routes');
define('PATH_STORAGE', PATH_BASE . '/storage');
define('PATH_LIBRARIES', PATH_APP . '/Libraries');

if (config("app.env") == 'local') {
    define('DOMAIN_WEB', 'powerfulfin.kezhanwang.cn');                //官网域名
    define('DOMAIN_INNER', 'inner.powerfulfin.kezhanwang.cn');        //内部调用域名
    define('DOMAIN_ORG', 'devo.powerfulfin.com');              //机构管理后台域名
} else if (config("app.env") == 'dev') {
    define('DOMAIN_WEB', 'powerfulfin.kezhanwang.cn');                //官网域名
    define('DOMAIN_INNER', 'inner.powerfulfin.kezhanwang.cn');        //内部调用域名
    define('DOMAIN_ORG', 'o.powerfulfin.kezhanwang.cn');              //机构管理后台域名
} else {
    define('DOMAIN_WEB', 'powerfulfin.com');                //官网域名
    define('DOMAIN_INNER', 'inner.powerfulfin.com');        //内部调用域名
    define('DOMAIN_ORG', 'o.powerfulfin.com');              //机构管理后台域名
}


/**
 * 数据状态，SUCCESS FAIL
 */
define('STATUS_SUCCESS', 'SUCCESS');
define('STATUS_FAIL', 'FAIL');

/**
 * 设备类型
 */
define('PHONE_TYPE_ANDROID', 'Android');
define('PHONE_TYPE_IOS', 'IOS');

/**
 * 资金方配置
 */
define('RESOURCE_JCFC', 1);
define('RESOURCE_JCFC_COMPANY', '晋商消费金融股份有限公司');
define('RESOURCE_FCS', 2);
define('RESOURCE_FCS_COMPANY', '富登小额贷款（重庆）有限公司');
define('RESOURCE_FCS_SC', 3);
define('RESOURCE_FCS_SC_COMPANY', '富登小额贷款(四川)有限公司');
