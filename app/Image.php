<?php namespace Chan;

use \upload;

class Image
{
    static $imageUploadRatio   = 1000;
    static $imageUploadAllowed = array('image/*');
    static $imageUploadSize    = 2097152;
    static $imageLang          = 'zh_TW';
    static $thumbDebug         = false;
    static $_langFileNotExist  = '檔案不存在';

    /**
     * Image Upload
     *
     * @param string $path path
     * @param string $file file name
     * @return array
     **/
    public static function upload($path = '/', $img = '')
    {
        $error = '';
        $imgName = '';
        $imgName = date('YmdHis') . rand(1000, 9999);
        $handle = new upload($_FILES[$img], static::$imageLang);
        $handle->file_new_name_body = $imgName;
        $handle->file_max_size = static::$imageUploadSize;
        $handle->allowed = static::$imageUploadAllowed;
        $handle->jpeg_quality = 100;
        $handle->image_resize = true;
        $handle->image_x = static::$imageUploadRatio;
        $handle->image_y = static::$imageUploadRatio;
        $handle->image_ratio = true;
        $handle->image_ratio_no_zoom_in = true;
        $handle->process($path);

        if ($handle->processed === false) {
            $error = $handle->error;
        }

        $handle->clean();

        return array(
            'err'        => $error,
            'img'        => $handle->file_dst_name,
            'originName' => $handle->file_src_name,
            'path'       => $path
        );
    }

    /**
     * Make fit thumbnail
     *
     * @param string $dir directory
     * @param string $img image name
     * @param integer $width image width
     * @param integer $height image height
     * @param string $noFile message when file not exiest
     * @param string $nameOnly return string when true
     * @return mixed
     */
    public static function fitThumb($dir, $img, $width = 0, $height = 0, $noFile = '', $nameOnly = false)
    {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%sx%s_fit',
            $body,
            $width,
            $height);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;
        $result = null;

        if ($noFile === '') {
            $noFile = static::$_langFileNotExist;
        }

        if (file_exists($dir . $img) === false || $img === '') {
            return $noFile;
        }

        if (file_exists($thumbName) === true) {
            if ($nameOnly === true) {
                $result = $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                $result = sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            $foo = new upload($dir . $img);
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;
            $foo->image_ratio_crop = 'T';

            if ($width === 0 && $height !== 0) {
                $foo->image_y = $height;
                $foo->image_ratio_x = true;
            } elseif ($width !== 0 && $height === 0) {
                $foo->image_x = $width;
                $foo->image_ratio_y = true;
            } else {
                $foo->image_x = $width;
                $foo->image_y = $height;
                $foo->image_ratio = true;
            }

            $foo->process($thumbDir);

            if ($foo->processed === true) {
                if ($nameOnly === true) {
                    $result = $thumbName;
                } else {
                    $result = sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $foo->image_dst_x,
                        $foo->image_dst_y);
                }
            } else {
                if (static::$thumbDebug === true) {
                    $result = $foo->error;
                }
            }
        }

        return $result;
    }

    /**
     * Make square thumbnail
     *
     * @param string $dir directory
     * @param string $img image name
     * @param integer $ratio image ratio
     * @param string $noFile message when file not exiest
     * @param string $nameOnly return string when true
     * @return mixed
     */
    public static function squareThumb($dir, $img, $ratio = 150, $noFile = '', $nameOnly = false)
    {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%s_square',
            $body,
            $ratio);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;
        $result = null;

        if ($noFile === '') {
            $noFile = static::$_langFileNotExist;
        }

        if (file_exists($dir . $img) === false || $img === '') {
            return $noFile;
        }

        if (file_exists($thumbName) === true) {
            if ($nameOnly === true) {
                $result = $thumbName;
            } else {
                $result = sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $ratio,
                    $ratio);
            }
        } else {
            $foo = new upload($dir . $img, static::$imageLang);
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;
            $foo->image_x = $ratio;
            $foo->image_y = $ratio;
            $foo->image_ratio_crop = 'T';
            $foo->image_ratio = 'true';
            $foo->process($thumbDir);

            if ($foo->processed === true) {
                if ($nameOnly === true) {
                    $result = $thumbName;
                } else {
                    $result = sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $ratio,
                        $ratio);
                }
            } else {
                if (static::$thumbDebug === true) {
                    $result = $foo->error;
                }
            }
        }

        return $result;
    }

    /**
     * Make thumbnail
     *
     * @param string $dir directory
     * @param string $img image name
     * @param integer $width image width
     * @param integer $height image height
     * @param string $noFile message when file not exiest
     * @param string $nameOnly return string when true
     * @return mixed
     */
    public static function thumb($dir, $img, $width = 0, $height = 0, $noFile = '', $nameOnly = false)
    {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%sx%s_thumb',
            $body,
            $width,
            $height);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;
        $result = null;

        if ($noFile === '') {
            $noFile = static::$_langFileNotExist;
        }

        if (file_exists($dir . $img) === false || $img === '') {
            return $noFile;
        }

        if (file_exists($thumbName) === true) {
            if ($nameOnly === true) {
                $result = $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                $result = sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            $foo = new upload($dir . $img, static::$imageLang);
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;

            if ($width === 0 && $height !== 0) {
                $foo->image_y = $height;
                $foo->image_ratio_x = true;
            } elseif ($width !== 0 && $height === 0) {
                $foo->image_x = $width;
                $foo->image_ratio_y = true;
            } else {
                $foo->image_x = $width;
                $foo->image_y = $height;
                $foo->image_ratio = true;
            }

            $foo->process($thumbDir);

            if ($foo->processed === true) {
                if ($nameOnly === true) {
                    $result = $thumbName;
                } else {
                    $result = sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $foo->image_dst_x,
                        $foo->image_dst_y);
                }
            } else {
                if (static::$thumbDebug === true) {
                    $result = $foo->error;
                }
            }
        }

        return $result;
    }
}
