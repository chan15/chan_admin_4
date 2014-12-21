<?php namespace Chan;

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

    /**
     * Delete file
     *
     * @param string $path
     * @return boolean
     */
    public static function delete($path = null)
    {
        if (file_exists($path) === true) {
            return @unlink($path);
        }

        return false;
    }

    /**
     * Copy file
     *
     * @param string $path
     * @param string $newPath
     * @param boolean $rename
     * @return boolean
     */
    public static function copy($path = null, $newPath = null, $rename = false)
    {
        if (file_exists($path) === true) {
            $pathInfo = pathinfo($path);

            if ($rename === false) {
                $newFile = $newPath . $pathInfo['basename'];
            } else {
                $newFile = $newPath . date('YmdHis') . rand(10000, 99999) . '.' . $pathInfo['extension'];
            }

            if (static::checkDir($newPath) === true) {
                return @copy($path, $newFile);
            }
        }

        return false;
    }

    /**
     * Move file
     *
     * @param string $path
     * @param string $newPath
     * @param boolean $rename
     * @return boolean
     */
    public static function move($path = null, $newPath = null, $rename = false)
    {
        if (file_exists($path) === true) {
            $pathInfo = pathinfo($path);

            if ($rename === false) {
                $newFile = $newPath . $pathInfo['basename'];
            } else {
                $newFile = $newPath . date('YmdHis') . rand(10000, 99999) . '.' . $pathInfo['extension'];
            }

            if (static::checkDir($newPath) === true) {
                return @rename($path, $newFile);
            }
        }

        return false;
    }

    /**
     * Check if path exists, if not create one
     *
     * @param string $path
     * @return boolean
     */
    public static function checkDir($path = null)
    {
        if (is_dir($path) === false) {
            return @mkdir($path, 0777, true);
        }

        return true;
    }
}
