<?php
namespace app\admin\lib;

class SqlFile
{
    /**
     * 从sql文件获取纯sql语句
     * @param  string $sql_file sql文件路径
     * @param  bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
     * @param  array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀"my_"替换成"me_"
     *         这种前缀替换方法不一定准确，比如正常内容内有跟前缀相同的字符，也会被替换
     * @return mixed
     */
    public static function getSqlFromFile($sql_file = '', $string = false, $replace = [])
    {
        if (!file_exists($sql_file)) {
            return false;
        }

        // 读取sql文件内容
        $handle = self::read_file($sql_file);
        // 分割语句
        $handle = self::parseSql($handle, $string, $replace);

        return $handle;
    }

    /**
     * 分割sql语句
     * @param  string $content sql内容
     * @param  bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
     * @param  array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀my_替换成me_
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    public static function parseSql($content = '', $string = false, $replace = [])
    {
        $sql = str_replace("\r", "\n", $content);
        $sql = explode(";\n", $sql);

        if (!empty($replace)){
            //替换表前缀
            $orginal_prefix = array_key_first($replace);
            $replace_prefix = $replace[$orginal_prefix];
            $sql = str_replace(" `{$orginal_prefix}", " `{$replace_prefix}", $sql);
        }
        return $sql;
    }

    /**
     * 读取文件内容
     * @param string $filename  文件名
     * @return string 文件内容
     */
    public static function read_file($filename) {
        $content = '';
        if(function_exists('file_get_contents')) {
            @$content = file_get_contents($filename);
        } else {
            if(@$fp = fopen($filename, 'r')) {
                @$content = fread($fp, filesize($filename));
                @fclose($fp);
            }
        }
        return $content;
    }
}