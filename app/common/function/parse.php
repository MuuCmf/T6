<?php


/**
 * 限制字符串长度
 * @param        $str
 * @param int $length
 * @param string $ext
 * @return string
 */
function getShort($str, $length = 40, $ext = '')
{
    $str = htmlspecialchars($str);
    $str = strip_tags($str);
    $str = htmlspecialchars_decode($str);
    $strlenth = 0;
    $out = '';
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
    foreach ($match[0] as $v) {
        preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $v, $matchs);
        if (!empty($matchs[0])) {
            $strlenth += 1;
        } elseif (is_numeric($v)) {
            //$strlenth +=  0.545;  // 字符像素宽度比例 汉字为1
            $strlenth += 0.5; // 字符字节长度比例 汉字为1
        } else {
            //$strlenth +=  0.475;  // 字符像素宽度比例 汉字为1
            $strlenth += 0.5; // 字符字节长度比例 汉字为1
        }

        if ($strlenth > $length) {
            $output .= $ext;
            break;
        }

        $output .= $v;
    }
    return $output;
}


/**带省略号的限制字符串长
 * @param $str
 * @param $num
 * @return string
 */
function getShortSp($str, $num)
{
    if (utf8_strlen($str) > $num) {
        $tag = '...';
    }
    $str = getShort($str, $num) . $tag;
    return $str;
}

function utf8_strlen($string = null)
{
// 将字符串分解为单元
    preg_match_all("/./us", $string, $match);
// 返回单元个数
    return count($match[0]);
}


function replace_attr($content)
{
    // 阻止代码部分被过滤 过滤前
    preg_match_all('/\<pre .*?\<\/pre\>/si',$content,$matches);
    $pattens=array();
    foreach($matches[0] as $key=>$val){
        $pattens[$key]='{$pre}_'.$key;
        $content=str_replace($val,$pattens[$key],$content);
    }
    //阻止代码部分被过滤 过滤前end

    $content = preg_replace("/class=\".*?\"/si", "", $content);
    $content = preg_replace("/id=\".*?\"/si", "", $content);
    $content = closetags($content);

    //阻止代码部分被过滤 过滤后
    $content=str_replace($pattens,$matches[0],$content);
    //阻止代码部分被过滤 过滤后end
    return $content;

}

function closetags($html)
{
    preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];

    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);

    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    $openedtags=array_diff($openedtags,array('br'));
    for ($i = 0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</' . $openedtags[$i] . '>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}

if (!function_exists('check_image_src')) {
    /**
     * check_image_src  判断链接是否为图片
     * @param $file_path
     * @return bool
     */
    function check_image_src($file_path)
    {
        if (!is_bool(strpos($file_path, 'http://'))) {
            $header = curl_get_headers($file_path);
            $res = strpos($header['Content-Type'], 'image/');
            return is_bool($res) ? false : true;
        } else {
            return true;
        }
    }
}

if (!function_exists('filter_image')) {
    /**
     * filter_image  对图片src进行安全过滤
     * @param $content
     * @return mixed
     */
    function filter_image($content)
    {
        preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/", $content, $arr); //匹配所有的图片
        if ($arr[1]) {
            foreach ($arr[1] as $v) {
                $check = check_image_src($v);
                if (!$check) {
                    $content = str_replace($v, '', $content);
                }
            }
        }
        return $content;
    }
}

/**
 * check_html_tags  判断是否存在指定html标签
 * @param $content
 * @param $tags
 * @return bool
 */
function check_html_tags($content, $tags = array())
{
    $tags = is_array($tags) ? $tags : array($tags);
    if (empty($tags)) {
        $tags = array('script', '!DOCTYPE', 'meta', 'html', 'head', 'title', 'body', 'base', 'basefont', 'noscript', 'applet', 'object', 'param', 'style', 'frame', 'frameset', 'noframes', 'iframe');
    }
    foreach ($tags as $v) {
        $res = strpos($content, '<' . $v);
        if (!is_bool($res)) {
            return true;
        }
    }
    return false;
}

if (!function_exists('filter_base64')) {
    /**
     * filter_base64   对内容进行base64过滤
     * @param $content
     * @return mixed
     */
    function filter_base64($content)
    {
        preg_match_all("/data:.*?,(.*?)\"/", $content, $arr); //匹配base64编码
        if ($arr[1]) {
            foreach ($arr[1] as $v) {
                $base64_decode = base64_decode($v);
                $check = check_html_tags($base64_decode);
                if ($check) {
                    $content = str_replace($v, '', $content);
                }
            }
        }
        return $content;
    }
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
if (!function_exists('msubstr')) {
    function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
    {
        if (function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice . '...' : $slice;
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int $size 大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++)
            $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

}

/**
 * cut_str  截取字符串
 * @param $search
 * @param $str
 * @param string $place
 * @return mixed
 */
if (!function_exists('cut_str')) {
    function cut_str($search,$str,$place=''){
        switch($place){
            case 'l':
                $result = preg_replace('/.*?'.addcslashes(quotemeta($search),'/').'/','',$str);
                break;
            case 'r':
                $result = preg_replace('/'.addcslashes(quotemeta($search),'/').'.*/','',$str);
                break;
            default:
                $result =  preg_replace('/'.addcslashes(quotemeta($search),'/').'/','',$str);
        }
        return $result;
    }
}

/**
 * 首字母转大写
 */
if (!function_exists('mb_ucfirst')) {

    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }

}

/**
 * t函数用于过滤标签，输出没有html的干净的文本
 * @param string text 文本内容
 * @return string 处理后内容
 */
if (!function_exists('text')) {
    function text($text, $addslanshes = false)
    {
        $text = nl2br($text);
        $text = real_strip_tags($text);
        if ($addslanshes)
            $text = addslashes($text);
        $text = trim($text);
        return $text;
    }
}
/**
 * 用于过滤不安全的html标签，输出安全的html
 * @param string $text 待过滤的字符串
 * @param string $type 保留的标签格式
 * @return string 处理后内容
 */
if (!function_exists('html')) {
    function html($text,$type = 'html')
    {
        // 无标签格式
        $text_tags = '';
        //只保留链接
        $link_tags = '<a>';
        //只保留图片
        $image_tags = '<img>';
        //只存在字体样式
        $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
        //标题摘要基本格式
        $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
        //兼容Form格式
        $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
        //内容等允许HTML的格式
        $html_tags = $base_tags . '<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
        //专题等全HTML格式
        $all_tags = $form_tags . $html_tags . '<!DOCTYPE><meta><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
        //过滤标签
        $text = real_strip_tags($text, ${$type . '_tags'});
        // 过滤攻击代码
        if ($type != 'all') {
            // 过滤危险的属性，如：过滤on事件lang js
            while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background[^-]|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
                $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
            }
            while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
                $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
            }
        }
        return $text;
    }
}

/**
 * 过滤标签
 */
if (!function_exists('real_strip_tags')) {
    function real_strip_tags($str, $allowable_tags = "")
    {
    // $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        return strip_tags($str, $allowable_tags);
    }
}

/**
 * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 * @param $pArray 一个二维数组
 * @param $pKey 数组的键的名称
 * @return 返回新的一维数组
 */
if (!function_exists('getSubByKey')) {
    function getSubByKey($pArray, $pKey = "", $pCondition = "")
    {
        $result = array();
        if (is_array($pArray)) {
            foreach ($pArray as $temp_array) {
                if (is_object($temp_array)) {
                    $temp_array = (array)$temp_array;
                }
                if (("" != $pCondition && $temp_array[$pCondition[0]] == $pCondition[1]) || "" == $pCondition) {
                    $result[] = (("" == $pKey) ? $temp_array : isset($temp_array[$pKey])) ? $temp_array[$pKey] : "";
                }
            }
            return $result;
        } else {
            return false;
        }
    }
}


/**
 * create_rand随机生成一个字符串
 * @param int $length 字符串的长度
 * @param string $type 类型
 * @return string
 */
if (!function_exists('create_rand')) {
    function create_rand($length = 8, $type = 'all')
    {
        $num = '0123456789';
        $letter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($type == 'num') {
            $chars = $num;
        } elseif ($type == 'letter') {
            $chars = $letter;
        } else {
            $chars = $letter . $num;
        }

        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }
}

/**
 * 比较数组 返回差集
 */
if (!function_exists('array_subtract')) {
    function array_subtract($a, $b)
    {
        return array_diff($a, array_intersect($a, $b));
    }
}

if (!function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}

if (!function_exists('is_json')) {
    /**
     * 判断字符串是否为 Json 格式
     *
     * @param string $data Json 字符串
     * @param bool $assoc 是否返回关联数组。默认返回对象
     * @param bool $htmlspecialchars_decode 是否进行html反转义
     * @return array|bool|object 成功返回转换后的对象或数组，失败返回 false
     */


    function is_json($data = '', $assoc = false, $htmlspecialchars_decode = false)
    {
        $data = json_decode($data, $assoc);
        if ($htmlspecialchars_decode) {
            if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
                return $data;
            }
        } else {
            if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
                return $data;
            }
        }
        return false;
    }
}

if (!function_exists('deep_in_array')) {
    /**
     * 多维数组中查询是否包含值
     * @param  [type] $value [description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    function deep_in_array($value, $array) {   
        foreach($array as $item) {   
            if(!is_array($item)) {   
                if ($item == $value) {  
                    return true;  
                } else {  
                    continue;   
                }  
            }   
                
            if(in_array($value, $item)) {  
                return true;      
            } else if(deep_in_array($value, $item)) {  
                return true;      
            }  
        }
        return false;   
    }
}

if (!function_exists('num2string')) {
    /**
     * 数字转友好显示： 如： 10000 -》 1w
     */
    function num2string($num) {
        if ($num >= 10000) {
            $num = number_format(round($num / 10000 * 100) / 100,1) .'w';
        } elseif($num >= 1000) {
            $num = number_format(round($num / 1000 * 100) / 100,1) . 'k';
        }
        return $num;
    }

}

if (!function_exists('create_uuid')) {
    function create_uuid(){
        
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
        return $uuid;
    }
}

if (!function_exists('create_guid')) {
    function create_guid($namespace = '') {     
        static $guid = '';
        $uid = uniqid("", true);
            $data = $namespace;
            $data .= $_SERVER['REQUEST_TIME'];
            $data .= $_SERVER['HTTP_USER_AGENT'];
            $data .= $_SERVER['SERVER_ADDR'];
            $data .= $_SERVER['SERVER_PORT'];
            $data .= $_SERVER['REMOTE_ADDR'];
            $data .= $_SERVER['REMOTE_PORT'];
            $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
            $guid =    
                    substr($hash,  0,  8) . 
                    '-' .
                    substr($hash,  8,  4) .
                    '-' .
                    substr($hash, 12,  4) .
                    '-' .
                    substr($hash, 16,  4) .
                    '-' .
                    substr($hash, 20, 12);
                return $guid;
    }
}

if (!function_exists('create_unique')) {
    /**
     * 生成唯一标识符
     */
    function create_unique(){
        $data = $_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].time().rand();
        return sha1($data);
    }
}

if (!function_exists('build_order_no')) {
    /**
     * 生成唯一订单号
     */
    function build_order_no(){
        return date('Ymd').substr(microtime(),2,6).sprintf('%04d', mt_rand(0, 9999));
    }
}

if (!function_exists('build_serial_no')) {
    /**
     * 生成唯一流水号
     */
    function build_serial_no(){
        return build_order_no();
    }
}

if (!function_exists('emoji_encode')) {
    function emoji_encode($str){
        $strEncode = '';

        $length = mb_strlen($str,'utf-8');

        for ($i=0; $i < $length; $i++) {
            $_tmpStr = mb_substr($str,$i,1,'utf-8');
            if(strlen($_tmpStr) >= 4){
                $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
            }else{
                $strEncode .= $_tmpStr;
            }
        }

        return $strEncode;
    }
}

if (!function_exists('emoji_decode')) {
    //对emoji表情转反义
    function emoji_decode($str)
    {
        $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function ($matches) {
            return rawurldecode($matches[1]);
        }, $str);
        return $strDecode;
    }
}

if (!function_exists('filter_emoji')) {
    // 过滤掉字符串中emoji表情
    function filter_emoji($str)
    {
        $str = preg_replace_callback( '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
        $str);

    return $str;
    }
}



