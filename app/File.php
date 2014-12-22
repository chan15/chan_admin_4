<?php namespace Chan;

use \upload;

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
        if ($path !== null) {
            return @unlink($path);
        }

        return false;
    }

    /**
     * Delete directory
     *
     * @param string $path
     * @return boolean
     */
    public static function deleteDir($path = null)
    {
        if ($path !== null) {
            $files = array_diff(scandir($path), array('.', '..'));

            if (count($files) > 0) {
                foreach ($files as $file) {
                    $target = $path . $file;
                    (is_dir($target) === true) ? static::deleteDir($target . '/') : @unlink($target);
                }
            }

            return @rmdir($path);
        }

        return false;
    }

    /**
     * Copy file
     *
     * @param string $path
     * @param string $newPath
     * @param boolean $rename
     * @return boolean|array
     */
    public static function copy($path = null, $newPath = null, $rename = false)
    {
        if (file_exists($path) === true) {
            $pathInfo = pathinfo($path);

            if ($rename === false) {
                $target = $newPath . $pathInfo['basename'];
            } else {
                $target = $newPath . date('YmdHis') . rand(10000, 99999) . '.' . $pathInfo['extension'];
            }

            if (static::checkDir($newPath) === true) {
                if (@copy($path, $target) === true) {
                    return array(
                        'target' => $target
                    );
                }
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
     * @return boolean|array
     */
    public static function move($path = null, $newPath = null, $rename = false)
    {
        if (file_exists($path) === true) {
            $pathInfo = pathinfo($path);

            if ($rename === false) {
                $target = $newPath . $pathInfo['basename'];
            } else {
                $target = $newPath . date('YmdHis') . rand(10000, 99999) . '.' . $pathInfo['extension'];
            }


            if (static::checkDir($newPath) === true) {
                if (@rename($path, $target) === true) {
                    return array(
                        'target' => $target
                    );
                }
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
