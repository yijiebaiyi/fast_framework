<?php
declare (strict_types=1);

namespace fast\util;


use fast\Exception;

class File
{
    /**
     * 检查文件是否存在
     * @param $file
     * @return bool|string
     */
    public static function exists($file)
    {
        $file = trim($file);
        if (!$file) {
            return false;
        }

        $abs = ($file[0] == '/' || $file[0] == '\\' || $file[1] == ':');
        if ($abs && file_exists($file)) {
            return $file;
        } elseif (strpos($file, 'http://') === 0) {//远程文件
            return self::remoteExists($file);
        }

        return false;
    }

    /**
     * 检测远程文件是否存在
     * @param string $file 文件路径
     * @return bool
     */
    private static function remoteExists(string $file): bool
    {
        //检测输入
        $file = trim($file);

        if (empty($file)) {
            return false;
        }

        $urlArr = parse_url($file);
        if (!is_array($urlArr) || empty($urlArr)) {
            return false;
        }

        //获取请求数据
        $host = $urlArr['host'];
        $path = $urlArr['path'] . "?" . (empty($urlArr['query']) ? '' : $urlArr['query']);
        $port = isset($urlArr['port']) ? $urlArr['port'] : "80";

        //连接服务器
        $fp = fsockopen($host, $port, $errNo, $errStr, 30);
        if (!$fp) {
            return false;
        }

        //构造请求协议
        $requestStr = "GET " . $path . " HTTP/1.1\r\n";
        $requestStr .= "Host: " . $host . "\r\n";
        $requestStr .= "Connection: Close\r\n\r\n";

        //发送请求
        fwrite($fp, $requestStr);
        $firstHeader = fgets($fp, 1024);
        fclose($fp);

        //判断文件是否存在
        if (!trim($firstHeader)) {
            return false;
        }
        if (strpos($firstHeader, '200') === false) {
            return false;
        }
        return true;
    }

    /**
     * 导入文件
     * @param $file
     * @return mixed
     * @throws \Exception
     */
    public static function load($file)
    {
        if (!self::exists($file)) {
            throw new \Exception('File does not exist or is not readable: ' . $file);
        }

        if (!is_readable($file)) {
            throw new \Exception('File does not readable: ' . $file);
        }

        return include $file;
    }

    /**
     * 获取文件
     * @param $file
     * @param false $intoAnArray
     * @return array|false|string
     */
    public static function get($file, $intoAnArray = false)
    {
        if (!self::exists($file)) {
            return false;
        }

        if (false == $intoAnArray) {
            return file_get_contents($file);
        } else {
            return file($file);
        }
    }

    /**
     * 写入文件
     * @param $content
     * @param $path
     * @param int $flags
     * @return false|int
     */
    public static function write($content, $path, $flags = 0)
    {
        $path = trim($path);
        if (empty($path)) {
            trigger_error('$path must to be set!');

            return false;
        }

        $dir = dirname($path);
        if (!self::exists($dir)) {
            if (false == self::mkdir($dir)) {
                trigger_error('filesystem is not writable: ' . $dir);

                return false;
            }
        }
        $path = str_replace("//", "/", $path);

        return file_put_contents($path, $content, ((empty($flags)) ? (LOCK_EX) : $flags));
    }

    /**
     * 复制dir
     * @param $source
     * @param $dest
     * @param false $overwrite
     * @return bool
     * @throws Exception
     */
    public function copyDir($source, $dest, $overwrite = false): bool
    {
        if (!is_dir($dest)) {
            if (!is_writable(dirname($dest))) {
                throw new Exception('filesystem not writable:' . dirname($dest));
            }
            mkdir($dest);
        }
        if ($handle = opendir($source)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $path = $source . '/' . $file;
                    if (self::exists($path)) {
                        if (!self::exists($dest . '/' . $file) || $overwrite) {
                            if (!@copy($path, $dest . '/' . $file)) {
                                throw new Exception('filesystem not writable:' . $dest . '/' . $file);
                            }
                        }
                    } elseif (is_dir($path)) {
                        if (!is_dir($dest . '/' . $file)) {
                            if (!is_writable(dirname($dest . '/' . $file))) {
                                throw new Exception('filesystem not writable:' . dirname($dest . '/' . $file));
                            }
                            mkdir($dest . '/' . $file); // make subdirectory before subdirectory is copied
                        }
                        self::copyDir($path, $dest . '/' . $file, $overwrite); //recurse
                    }
                }
            }
            closedir($handle);
        }
        return true;
    }

    /**
     * 删除文件
     * @param $path
     * @param false $recursive
     * @return bool
     */
    public static function rm($path, $recursive = false): bool
    {
        if (!self::exists($path)) {
            trigger_error('File does not exist or is not readable:' . $path);

            return false;
        }
        if (is_file($path)) {
            return unlink($path);
        } elseif (is_dir($path)) {
            $handle = opendir($path);
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' and $file != '..') {
                    $fullpath = $path . $file;
                    if (is_dir($fullpath) && $recursive) {
                        self::rm($fullpath, $recursive);
                    } else {
                        unlink($fullpath);
                    }
                }
            }

            closedir($handle);
            rmdir($path);

            return true;
        }
        return false;
    }

    /**
     * 生成目录
     * @param $path
     * @param int $chmod
     * @param bool $recursive
     * @return bool
     */
    public static function mkdir($path, $chmod = 0777, $recursive = true)
    {
        mkdir($path, $chmod, $recursive);

        return true;
    }

    /**
     * 列出文件列表
     * @param string $__dir 路径 默认为当前路径
     * @param string $__pattern 文件类型 默认为所有类型
     * @return array
     */
    public static function ls($__dir = './', $__pattern = '*.*'): array
    {
        settype($__dir, 'string');
        settype($__pattern, 'string');

        $__ls = array();
        $__regexp = preg_quote($__pattern, '/');
        $__regexp = preg_replace('/[\\x5C][\x2A]/', '.*', $__regexp);
        $__regexp = preg_replace('/[\\x5C][\x3F]/', '.', $__regexp);

        if (is_dir($__dir)) {
            if (($__dir_h = @opendir($__dir)) !== false) {
                while (($__file = readdir($__dir_h)) !== false) {
                    if ('.' != $__file && '..' != $__file) {
                        if (preg_match('/^' . $__regexp . '$/', $__file)) {
                            array_push($__ls, $__file);
                        }
                    }
                }
                closedir($__dir_h);
                sort($__ls, SORT_STRING);
            }
        }
        return $__ls;
    }

    /**
     * 获取文件扩展名,不带点
     * @param string $file 文件名
     * @return string
     */
    public static function getExtName(string $file): string
    {
        $extension = explode(".", basename($file));
        return end($extension);
    }

    /**
     * 获取文件名
     * @param $file
     * @return string
     */
    public static function getMainName(string $file): string
    {
        $offset = strrpos($file, '/');
        $len = strrpos($file, '.');

        if ($offset === false && $len === false) {
            return $file;
        }

        return substr($file, strrpos($file, '/'), strrpos($file, '.'));
    }

    /**
     * 查找最深目录
     * @param      $root
     * @param bool $hasHidden
     * @return array
     */
    public static function findSubDirs($root, $hasHidden = false): array
    {
        return self::_findSubDirs($root, $hasHidden);
    }

    private static function _findSubDirs($root, $hasHidden = false, &$subdirs = [])
    {
        if (!is_dir($root)) {
            return false;
        }

        $fullPath = rtrim($root, '/');
        $fullPath .= $hasHidden ? '/.*' : '/*';
        $dirs = glob($fullPath);
        unset($dirs['.'], $dirs['..']);

        $subdirs[] = $root;

        if (empty($dirs)) {
            return $subdirs;
        }

        $hasChildDir = false;
        foreach ($dirs as $_dir) {
            if (!is_dir($_dir) || is_link($_dir)) {
                continue;
            }
            $hasChildDir = true;
            self::_findSubDirs($_dir, $hasHidden, $subdirs);
        }

        if (!$hasChildDir) {
            return $subdirs;
        }

        return $subdirs;
    }
}
