<?php namespace Chan;

use upload;

class File
{
    static $fileUploadSize = 5242880;
    static $fileMimeCheck  = true;

    /**
     * File upload
     *
     * @param string $path path
     * @param string $file file name
     * @return array
     **/
    public static function upload($path = '/', $file = '')
    {
        $error = '';
        $imgName = '';
        $fileName = date('YmdHis') . rand(1000, 9999);
        $handle = new upload($_FILES[$file]);
        $handle->file_new_name_body = $fileName;
        $handle->file_max_size = static::$fileUploadSize;
        $handle->mime_check = static::$fileMimeCheck;
        $handle->process($path);

        if ($handle->processed === false) {
            $error = $handle->error;
        }

        $handle->clean();

        return array(
            'err'        => $error,
            'file'       => $handle->file_dst_name,
            'extension'  => $handle->file_src_name_ext,
            'originName' => $handle->file_src_name,
            'path'       => $path
        );
    }
}
