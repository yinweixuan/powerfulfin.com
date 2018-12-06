<?php

namespace App\Components;
/**
 * 数组的帮助对象
 * @author haoxiang<haoxiang@kezhanwang.cn>
 */
class ArrayUtil
{
    /**
     * 滤掉数组每个项的空格
     * @param $arr
     * @return array
     */
    public static function trimArray($arr)
    {
        $ret = array();
        foreach ($arr as $k => $a) {
            if (is_array($a)) {
                $ret[$k] = ArrayUtil::trimArray($a);
            } else if (is_string($a)) {
                $ret[$k] = trim($a);
            } else {
                $ret[$k] = $a;
            }
        }
        return $ret;
    }

    /**
     * 检查个数组中所有元素是否在字符串中出现
     * @param type $search
     * @param type $text
     * @return bool|false|int
     */
    public static function searchInText($search, $text)
    {
        if (is_array($search)) {
            $res = preg_match("/" . implode('|', $search) . "/", $text);
        } else {
            $res = strpos($text, $search);
            if ($res === 0) {
                $res = true;
            }
        }
        if ($res) {
            $res = true;
        } else {
            $res = false;
        }
        return $res;
    }

    /**
     * 合并两个数组
     * @param type $arr1
     * @param type $arr2
     * @return array
     */
    public static function arrayMerge($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) {
            return array();
        }
        $ret = $arr1;
        foreach ($arr2 as $key => $val) {
            $ret[$key] = $val;
        }
        return $ret;
    }

    /**
     * 获得二维数组中某一个key的值的数组
     * @param type $arr
     * @param type $key
     * @return type
     */
    public static function getSomeKey($arr, $key, $unique = false)
    {
        $ret = array();
        $tmpArr = array();
        foreach ($arr as $k => $a) {
            if (isset($a[$key])) {
                $ret[$k] = $a[$key];
            }
        }
        if ($unique) {
            $ret = array_unique($ret);
        }
        return $ret;
    }

    /**
     * 去除里面的空元素
     * @param $arr
     * @return array
     */
    public static function escapeEmpty($arr)
    {
        $ret = array();
        foreach ($arr as $key => $val) {
            if ($val !== null && $val !== '') {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    /**
     * 将二维数组某一维的值当做key
     * @param $arr
     * @param $key
     * @param bool $replaceOld
     * @return array
     */
    public static function addKeyToArray($arr, $key, $replaceOld = true)
    {
        if (empty($arr)) {
            return $arr;
        }
        $ret = array();
        foreach ($arr as $k => $val) {
            if (isset($val[$key])) {
                $newKey = $val[$key];
            } else {
                $newKey = $k;
            }
            if ($replaceOld == false) {
                if (!isset($ret[$newKey])) {
                    $ret[$newKey] = $val;
                }
            } else {
                $ret[$newKey] = $val;
            }
        }
        return $ret;
    }

    /**
     * 按照字符串长度排序
     * @param $arr
     * @param int $order
     * @param string $encoding
     * @return array
     */
    public static function sortByStrlen($arr, $order = SORT_ASC, $encoding = 'UTF-8')
    {
        $lenArr = array();
        foreach ($arr as $key => $val) {
            $lenArr[$key] = mb_strlen($val, $encoding);
        }
        if ($order == SORT_DESC) {
            arsort($lenArr);
        } else {
            asort($lenArr);
        }
        $ret = array();
        foreach ($lenArr as $key => $val) {
            $ret[] = $arr[$key];
        }
        return $ret;
    }

    /**
     * 按长度区间进行截取
     * @param $arr
     * @param $max
     * @param int $min
     * @param string $encoding
     * @return array
     */
    public static function escapeByStrlen($arr, $max, $min = 0, $encoding = 'UTF-8')
    {
        $ret = array();
        foreach ($arr as $key => $val) {
            $len = mb_strlen($val, $encoding);
            if ($len >= $min && $len <= $max) {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    /**
     * 分页截取数组,保留key
     * @param $arr
     * @param $p
     * @param $ps
     * @return array
     */
    public static function pageWithKey($arr, $p, $ps)
    {
        $ret = array();
        if ($p < 1) {
            $p = 1;
        }
        $begin = ($p - 1) * $ps;
        $keys = array_slice(array_keys($arr), $begin, $ps);
        $vals = array_slice(array_values($arr), $begin, $ps);
        if (empty($keys) || empty($vals)) {
            return $ret;
        }
        $ret = array_combine($keys, $vals);
        return $ret;
    }

    /**
     * 按照分号分隔,返回int数组
     * @param $str
     * @param $separator
     * @return array
     */
    public static function explodeInt($str, $separator = SEPARATOR_BU)
    {
        $ret = array();
        $tmpArr = explode($separator, $str);
        foreach ($tmpArr as $i) {
            if (is_numeric($i)) {
                $ret[] = $i;
            }
        }
        return $ret;
    }

    /**
     * 从数组中随机选择几个值
     * @param $arr
     * @param $needCount
     * @param bool $keepKey
     * @return array
     */
    public static function random($arr, $needCount, $keepKey = true)
    {
        $ret = array();
        $totalCount = count($arr);
        if ($totalCount <= $needCount) {
            return $arr;
        }
        $keys = array_keys($arr);
        $randomKeys = array();
        $randomCount = 0;
        while ($randomCount < $needCount) {
            $tmpKey = rand(0, $totalCount - 1);
            if (!isset($randomKeys[$tmpKey])) {
                $randomKeys[$tmpKey] = $keys[$tmpKey];
                $randomCount++;
            }
        }
        foreach ($randomKeys as $k) {
            if ($keepKey) {
                $ret[$k] = $arr[$k];
            } else {
                $ret[] = $arr[$k];
            }
        }
        return $ret;
    }

    /**
     * 检查两个数组的不同.首先比较key是不是缺,然后同一个key是不是值都一样
     * @param $oldArr
     * @param $newArr
     * @return array
     */
    public static function checkDiff($oldArr, $newArr)
    {
        $ret = array(
            'lack' => array(),
            'new' => array(),
            'diff' => array(),
        );

        foreach ($oldArr as $oldKey => $oldVal) {
            if (isset($newArr[$oldKey])) {
                if ($oldArr[$oldKey] == $newArr[$oldKey]) {
                } else {
                    $ret['diff'][$oldKey] = $newArr[$oldKey];
                }
                unset($newArr[$oldKey]);
            } else {
                $ret['lack'][$oldKey] = $oldVal;
            }
        }
        $ret['increase'] = $newArr;

        return $ret;
    }

    /**
     * 获得二维数组中一部分key的值,其他值舍弃
     * @param $arr
     * @param $keys
     * @return array
     */
    public static function getArrOfKeys($arr, $keys)
    {
        $ret = array();
        foreach ($arr as $k => $v) {
            $ret[$k] = array();
            foreach ($keys as $needKey) {
                if (!isset($v[$needKey])) {
                    $ret[$k][$needKey] = null;
                } else {
                    $ret[$k][$needKey] = $v[$needKey];
                }
            }
        }
        return $ret;
    }

    /*
     * 给定的二维数组按照指定的键值进行排序
     */
    public static function sortArray($array, $keyid, $order = 'asc', $type = 'number')
    {
        if (is_array($array)) {
            foreach ($array as $val) {
                $order_arr[] = $val[$keyid];
            }
            $order = ($order == 'asc') ? SORT_ASC : SORT_DESC;
            $type = ($type == 'number') ? SORT_NUMERIC : SORT_STRING;
            array_multisort($order_arr, $order, $type, $array);
        }
    }

    /**
     * 给定的二维数组按照指定的键值进行排序
     * @param $arr
     * @param $key
     * @param string $order
     * @return array
     */
    public static function sortByKey($arr, $key, $order = 'asc')
    {
        $n = count($arr);
        if ($n == 1) {
            return $arr;
        }
        $temparr = $arr;
        $span = 1;
        while ($span < $n) {
            $start = 0;
            $sarr = array();
            while ($start < $n) {
                $i = $start;
                $j = $i + $span;
                $a = $start + $span > $n ? $n : $start + $span;
                $b = $start + $span * 2 > $n ? $n : $start + $span * 2;
                while ($i < $a || $j < $b) {
                    if ($order == 'asc') {
                        if ($j >= $b || $temparr[$i][$key] < $temparr[$j][$key] && $i < $a) {
                            $sarr[] = $temparr[$i];
                            $i++;
                        } else {
                            $sarr[] = $temparr[$j];
                            $j++;
                        }
                    } else {
                        if ($j >= $b || $temparr[$i][$key] > $temparr[$j][$key] && $i < $a) {
                            $sarr[] = $temparr[$i];
                            $i++;
                        } else {
                            $sarr[] = $temparr[$j];
                            $j++;
                        }
                    }
                }
                $start += $span * 2;
            }
            $span *= 2;
            $temparr = $sarr;
        }
        return $sarr;
    }

    /**
     * 数组降维
     * @param $arr
     * @param null $key
     * @return array
     */
    public static function arrReduce($arr, $key = null)
    {
        $result = array();
        if ($key) {
            foreach ($arr as $v) {
                $result[] = $v[$key];
            }
        } else {
            foreach ($arr as $v) {
                foreach ($v as $vv) {
                    $result[] = $vv;
                }
            }
        }
        return $result;
    }
}
