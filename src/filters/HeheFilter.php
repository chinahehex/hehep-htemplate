<?php
namespace htemplate\filters;

class HeheFilter
{
    /**
     * xss 危险标签过滤
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $str
     * @return string
     */
    public static function xss_filter($str)
    {
        $find = ['&','>','<','\'','"',];
        $replace = ['&amp;','&gt;','&lt;','&#39;','&#34;'];

        return str_replace($find,$replace,$str);
    }

    /**
     * 过滤所有危险字符
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $str
     * @return string
     */
    public static function safe_filter($str)
    {
        $str = static::xss_filter($str);

        return $str;
    }

    /**
     * 小物件
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $wclass 物件类路径
     * @param array $args 参数
     * @param bool $constructor 参数是否传入构造器
     * @return string
     */
    public static function w_filter($wclass,$args = [],$constructor = true)
    {
        list($widget_class,$widget_method,$new_class_status) = static::buildWidgetHandler($wclass);

        if ($constructor === true) {
            return call_user_func([new $widget_class($args),$widget_method]);
        } else {
            if ($new_class_status) {
                return call_user_func_array([new $widget_class(),$widget_method],$args);
            } else {
                return call_user_func([$widget_class,$widget_method],$args);
            }
        }
    }

    /**
     * 日期格式
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $format 日期格式类型
     * @param string|int $datetime 字符串日期或时间戳日期
     * @return string
     */
    public static function date_filter($format,$datetime)
    {
        if (empty($datetime)) {
            return '';
        }

        // 非时间戳,转成时间戳
        if (!is_numeric($datetime)) {
            $datetime = strtotime($datetime);
        }

        return date($format,$datetime);
    }

    /**
     * 截取字符串
     *<B>说明：</B>
     *<pre>
     *  支持中文截取
     *</pre>
     * @param string $str
     * @param int $start 截取的起始位置
     * @param int $length 截取的长度
     * @param boolean $suffix 是否需要添加...省略号
     * @return string
     */
    public static  function substr_filter($str, $start = 0, $length = null, $suffix = false,$charset = "utf-8")
    {

        if (function_exists ( "mb_substr" ))
            $slice = mb_substr ( $str, $start, $length, $charset );
        elseif (function_exists ( 'iconv_substr' )) {
            $slice = iconv_substr ( $str, $start, $length, $charset );
        } else {
            $re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all ( $re [$charset], $str, $match );
            $slice = join ( "", array_slice ( $match [0], $start, $length ) );
        }

        return $suffix ? $slice . '...' : $slice;
    }

    /**
     * 字典显示文本
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $dictlist
     * @param string $key 字典key
     * @param string $defualt 默认值
     * @return string
     */
    public static function dict_filter($dictlist = [],$key,$defualt = '')
    {
        if (isset($dictlist[$key])) {
            return $dictlist[$key];
        } else {
            return $defualt;
        }
    }

    /**
     * 字典显示文本
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $dictList
     * @param string $keys 字典key列表
     * @param string $glue 分隔符
     * @param string $defualt 默认值
     * @return string
     */
    public static function dicts_filter($dictList,$keys,$glue = ',',$defualt = '')
    {
        $dicIds = [];
        if (!is_array($keys)) {
            $dicIds = explode(',',$keys);
        } else {
            $dicIds = $keys;
        }

        $dicnames =[];
        foreach ($dictList as $id=>$name) {
            if (in_array($id,$dicIds)) {
                $dicnames[] = $name;
            }
        }

        if (empty($dicnames)) {
            return $defualt;
        }

        return implode($glue,$dicnames);
    }

    protected static function buildWidgetHandler($handler)
    {

        $handler = '\\' . str_replace(".","\\",$handler);

        $newClassStatus = false;
        if (strpos($handler,"@@") !== false) {
            list($handlerClass,$handlerMethod) = explode("@@",$handler);
        } else if (strpos($handler,"@") !== false) {
            list($handlerClass,$handlerMethod) = explode("@",$handler);
            $newClassStatus = true;
        } else {
            $handlerClass = $handler;
        }

        if (empty($handlerMethod)) {
            $handlerMethod = 'handle';
        }

        return [$handlerClass,$handlerMethod,$newClassStatus];
    }
}