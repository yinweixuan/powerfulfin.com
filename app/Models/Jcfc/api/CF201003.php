<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/11
 * Time: 上午11:08
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 表单资料缓存
 * 用户通过合作机构发起贷款申请，渠道机构审批通过之后，向晋商消费发起进件上传，并缓存表单信息。
 * Class CF201003
 */
class CF201003
{
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        $jcfcParams = array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
            //必填信息
            'custname' => $params['idcard_name'], // 姓名
            'idno' => $params['idcard'], //身份证号
            'indivmobile' => $params['phone'], //手机号
            'applydt' => date('Y-m-d'), //申请日期
            'loanaccount' => $params['school_bank_account'], //放款银行卡号
            'repayaccount' => $params['bank_account'], //还款银行卡号
            'productid' => $params['loan_type'], //市场产品编号
            //贷款信息
            'applyamt' => $params['money_apply'],
            'applytnrtyp' => 'M', //贷款周期 月
            'applytnr' => (string)$params['repay_need'], //贷款期数
            'duedayopt' => '2', //还款日类型
            'dueday' => '15', //还款日
            'purpose' => 'EDU', //贷款用途
            'loanfreq' => '1M', //还款间隔
            'cooprcde' => $params['jcfc_school_id'], //门店编号
            //身份信息
            'idvalidity' => date('Y-m-d', strtotime($params['idcard_expire'])), //身份证有效期
            'cooprcontactemail' => $params['email'],
            'mailopt' => 'A', //邮寄地址
            //家庭信息
            'indivmarital' => (string)self::getMarriageStatus($params['marriage_status']), //婚姻状况
            //居住信息
            'pptyinfo' => (string)self::getHouseStatus($params['house_status']),
            'liveprovince' => self::getParentAreaid($params['home_province'], $params['home_city']),
            'livecity' => self::getParentAreaid($params['home_city'], $params['home_area']),
            'livearea' => self::getParentAreaid($params['home_area']),
            'liveaddr' => $params['home_address'],
            //职业信息
            'positionopt' => (string)self::getPositionopt($params['work_type']), //社会身份
            'indivempname' => $params['work_name'], //工作单位
            'noworkdt' => ($params['entry_date'] == '0000-00-00' || empty($params['entry_date'])) ? '' : $params['entry_date'], //入职日期
            'indivmthinc' => number_format($params['monthly_income'] / 100, 2, '.', ''), //月收入
            'indivposition' => self::getPosition($params['position']),
            //教育信息
            'indivedu' => self::getDegree($params['degree']), //教育程度
            'schoolname' => !empty($params['university']) ? $params['university'] : '', //学校名称
            'studymajor' => !empty($params['major']) ? $params['major'] : '', //专业名称
            'schdate' => ($params['entrance_time'] == '0000' || empty($params['entrance_time'])) ? '' : $params['entrance_time'] . "-09-01", //入学时间
            'schoolleng' => ($params['education_system'] > 1 && $params['education_system'] <= 5) ? $params['education_system'] . "Y" : 'OTH', //学制
            //联系人信息[多个]
            'relarray' => array(
                array('relrelation' => self::getRelations($params['contact1_relation']), 'relname' => $params['contact1'], 'relmobile' => $params['contact1_phone']),
                array('relrelation' => self::getRelations($params['contact2_relation']), 'relname' => $params['contact2'], 'relmobile' => $params['contact2_phone']),
            ),
            //订单信息
            'goodsarray' => array(
                array(
                    'goodsname' => $params['course_name'], //商品名称
                    'goodskind' => '05', //商品类型
                    'goodsmodel' => $params['course_name'], //商品型号
                    'goodsnum' => 1, //商品数量
//                    'goodsprice' => $params['tuition'], //商品单价)
                    'goodsprice' => $params['money_apply'], //商品单价)
                ),
            ),
            'fstpay' => '0.00',
            'servicestartdt' => $params['course_open_time'],
            'servicendt' => $params['course_finish_time'],
        );
        return $jcfcParams;
    }

    /**
     * 修改特定的地区编码
     * @param type $areaid
     */
    public static function getParentAreaid($parent, $child = 0)
    {
        $map = array('110228' => '110200', '110229' => '110200',);
        if (array_key_exists($child, $map)) {
            return $map[$child];
        } else {
            return $parent;
        }
    }

    public static function getMarriageStatus($marriage_status)
    {
        $statusMapping = array(1 => 20, 2 => 20, 3 => 10, 4 => 40, 5 => 90);
        if (array_key_exists($marriage_status, $statusMapping)) {
            return $statusMapping[$marriage_status];
        } else {
            return 90;
        }
    }

    public static function getHouseStatus($house_status)
    {
        $houseMapping = array(1 => 80, 2 => 50, 3 => 30, 4 => 30, 5 => 20, 6 => 99);
        if (array_key_exists($house_status, $houseMapping)) {
            return $houseMapping[$house_status];
        } else {
            return 99;
        }
    }

    public static function getPosition($position)
    {
        $positionMapping = array('00' => '高层管理人员', '01' => '中层管理人员', '01' => '基层管理人员', '02' => '普通员工');
        if (in_array($position, $positionMapping)) {
            return array_search($position, $positionMapping);
        } else {
            return '02';
        }
    }

    public static function getDegree($degree)
    {
        $degreeMapping = array('初中及以下' => 40, '中专' => '30', '高中' => '30', '大专' => '20', '本科' => '10', '硕士' => '00', '博士及以上' => '00');
        if (array_key_exists($degree, $degreeMapping)) {
            return $degreeMapping[$degree];
        } else {
            return '50';
        }
    }

    public static function getRelations($relations)
    {
        $relationsMapping = array('父母' => '01', '配偶' => '06', '监护人' => '01', '子女' => '02', '兄弟姐妹' => '02', '亲属' => '02', '同事' => '03', '朋友' => '05', '同学' => '04', '其他' => '99');
        if (array_key_exists($relations, $relationsMapping)) {
            return $relationsMapping[$relations];
        } else {
            return '01';
        }
    }

    public static function getPositionopt($position)
    {
        $positionoptMapping = array(1 => 10, 2 => 40, 3 => 30);
        if (array_key_exists($position, $positionoptMapping)) {
            return $positionoptMapping[$position];
        } else {
            return 50;
        }
    }
}
