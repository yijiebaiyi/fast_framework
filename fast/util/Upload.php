<?php

namespace fast\util;


class Upload
{
    /**
     * @var string 上传错误信息
     */
    public string $error = "";

    /**
     * @var string 本地文件上传名称
     */
    public string $name = "";

    /**
     * @var string 文件后缀
     */
    public string $ext = "";

    /**
     * @var string 本地临时文件全路径
     */
    public string $tmpName = "";

    /**
     * @var string 类型
     */
    public string $type = "";

    /**
     * @var int 上传文件大小
     */
    public int $size = 0;

    /**
     * @var string 新文件名称
     */
    public string $filename = "";

    /**
     * @var string 新文件路径
     */
    public string $filepath = "";

    /**
     * 单文件上传
     * @param $filepath string 文件路径
     * @param $filename string 文件名称，不包含后缀
     * @return bool
     */
    public function uploadOne(string $filepath, string $filename): bool
    {
        if (empty($_FILES)) {
            $this->error = "没有文件上传";
            return false;
        }

        $currentFile = current($_FILES);
        $this->name = $currentFile['name'];
        $this->ext = File::getExtName($this->name);
        $this->tmpName = $currentFile['tmp_name'];
        $this->type = $currentFile['type'];
        $this->size = $currentFile['size'];
        $this->filename = $filename . "." . $this->ext;
        $this->filepath = $filepath;

        if ($currentFile['error'] != UPLOAD_ERR_OK) {
            switch ($currentFile['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->error = "上传文件大小超出php.ini限制";
                    return false;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->error = "上传文件大小超出form表单限制";
                    return false;
                case UPLOAD_ERR_PARTIAL:
                    $this->error = "只有部分文件被上传";
                    return false;
                case UPLOAD_ERR_NO_FILE:
                    $this->error = "没有文件被上传";
                    return false;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->error = "上传文件临时目录不存在";
                    return false;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->error = "文件写入失败";
                    return false;
                case UPLOAD_ERR_EXTENSION:
                    $this->error = "文件类型不允许";
                    return false;
                default:
                    $this->error = "未知错误";
                    return false;
            }
        }

        $fullName = $this->filepath . DIRECTORY_SEPARATOR . $this->filename;
        return move_uploaded_file($currentFile["tmp_name"], $fullName);
    }
}