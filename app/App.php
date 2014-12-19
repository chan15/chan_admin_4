<?php namespace Chan;

use \PHPExcel;

class App
{
    public $charset       = 'UTF-8';
    public $emailDebug    = false;
    public $emailFrom     = '';
    public $emailTo       = '';
    public $emailFromName = '';
    public $emailSubject  = '';
    public $emailContent  = '';
    public $meta          = '<meta http-equiv = "Content-Type" content = "text/html; charset = utf-8" />';
    public $loginPage     = 'login.php';
    public $conn          = 'default';

    /**
     * Check source url
     */
    public function checkSourceUrl()
    {
         if (stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
            die('Not the same domain');
         }
     }

    /**
     * Logout
     *
     * @param string url redirect page when logout
     */
    public function logout($url = 'index.php')
    {
        session_destroy();

        if (isset($_COOKIE) === true) {
            foreach ($_COOKIE as $name => $value) {
                $name = htmlspecialchars($name);
                setcookie($name , '');
            }
        }

        $this->reUrl($url);
    }

    /**
     * Save page to session as last visied page
     */
    public function lastPage ()
    {
        $_SESSION['lastPage'] = $this->retUri();
    }

    /**
     * Redirect
     *
     * @param string $url redirect url
     */
    public function reUrl($url)
    {
        header(sprintf('Location: %s', $url));
    }

    /**
     * Login level limitation
     *
     * @param array $level level (array(1, 2, ...))
     */
    public function loginLevel($level = array())
    {
        $this->loginNeed();

        if (in_array(@$_SESSION['level'], $level) === false) {
            $this->reUrl($this->loginPage);
        }
    }

    /**
     * Not login check
     * only allow user who is not login
     *
     * @param string $url redirect page
     */
    public function loginLimit($url = 'index.php')
    {
        if (isset($_SESSION['login']) === true) {
            $this->reUrl($url);
        }
    }

    /**
     * Login check
     * only allow user who is login
     *
     * @param string $url login page
     */
    public function loginNeed($url = null)
    {
        $this->lastPage();

        if ($url === null) {
            $url = $this->loginPage;
        }

        if (isset($_SESSION['login']) === false) {
            $this->reUrl($url);
        }
    }

    /**
     * Redirect by JavaScript
     *
     * @param string $string alert string
     * @param string $url redirect url
     */
    public function jsRedirect($string = null, $url = null)
    {
        echo $this->meta;
        echo '<script>';
        echo 'alert("' . $string . '");';
        echo 'window.location = "' . $url . '";';
        echo '</script>';
        exit;
    }

    /**
     * Export data as excel
     *
     * @param string $sql sql statement
     * @param array $titles title
     * @param array $fields field name
     * @param string $fileName file name
     * @param integer $width default excel column width
     **/
    public function makeExcel($sql = '', $titles = array(), $fields = array(), $fileName = null, $width = 12)
    {
        if ($fileName === null) {
            $fileName = date('YmdHis') . rand(1000, 9999);
        }

        $db = new Database($this->conn);
        $result = $db->myRow($sql);
        $excel = new PHPExcel;
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->getDefaultColumnDimension()->setWidth($width);
        $type = \PHPExcel_Cell_DataType::TYPE_STRING;

        foreach ($titles as $k => $v) {
            $excel->getActiveSheet()->setCellValueByColumnAndRow($k, 1, $v);
        }

        $rowIndex = 2;

        if ($result !== null) {
            foreach ($result as $row) {
                foreach ($fields as $k => $v) {
                    $excel->getActiveSheet()->getCellByColumnAndRow($k, $rowIndex)->setValueExplicit($row[$v], $type);
                }

                $rowIndex++;
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
    }

    /**
     * Convert escapte string
     *
     * @param string $string string need to be convert
     * @return string
     */
    public function convertEscape($string)
    {
        return str_replace('/', '\/', str_replace('"', '\"', $string));
    }

    /**
     * Required variable check
     *
     * @param array $variables required variables
     * @param string $url redirect url
     */
    public function reqVariable($variable = null, $url = 'index.php')
    {
        if (gettype($variable) === 'array') {
            foreach ($variables as $value) {
                if (isset($_GET[$value]) === false || empty($_GET[$value]) === true) {
                    $this->jsRedirect($this->_langUrlError, $url);
                    break;
                }
            }
        } else {
            if (isset($_GET[$variable]) === false || empty($_GET[$variable]) === true) {
                $this->jsRedirect($this->_langUrlError, $url);
                break;
            }
        }
    }

    /**
     * Temporary cookie id
     *
     * @param integer $day cookie exist day
     */
    public function tempCookieId($day = 7)
    {
        $time = time() + 3600 * 24 * $day;

        if (isset($_COOKIE['tempId']) === false) {
            setcookie('tempId', uniqid() . rand(10000, 99999), $time);
        }
    }

    /**
     * DateDiff
     *
     * $interval can be:
     * yyyy - Number of full years
     * q - Number of full quarters
     * m - Number of full months
     * y - Difference between day numbers
     * (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
     * d - Number of full days
     * w - Number of full weekdays
     * ww - Number of full weeks
     * h - Number of full hours
     * n - Number of full minutes
     * s - Number of full seconds (default)
     */
    public function dateDiff($interval, $datefrom, $dateto, $using_timestamps = false)
    {
        if ($using_timestamps === false) {
            $datefrom = strtotime($datefrom, 0);
            $dateto = strtotime($dateto, 0);
        }

        $difference = $dateto-$datefrom; // Difference in seconds

        switch($interval) {
            case 'yyyy': // Number of full years
                $years_difference = floor($difference / 31536000);

            if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom) + $years_difference) > $dateto) {
                $years_difference--;
            }

            if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto) - ($years_difference + 1)) > $datefrom) {
                $years_difference++;
            }

            $datediff = $years_difference;
            break;

        case "q": // Number of full quarters
            $quarters_difference = floor($difference / 8035200);

            while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                $months_difference++;
            }

            $quarters_difference--;
            $datediff = $quarters_difference;
            break;

        case "m": // Number of full months
            $months_difference = floor($difference / 2678400);

            while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                $months_difference++;
            }

            $months_difference--;
            $datediff = $months_difference;
            break;

        case 'y': // Difference between day numbers
            $datediff = date("z", $dateto)-date("z", $datefrom);
            break;

        case "d": // Number of full days
            $datediff = floor($difference / 86400);
            break;

        case "w": // Number of full weekdays
            $days_difference = floor($difference / 86400);
            $weeks_difference = floor($days_difference / 7); // Complete weeks
            $first_day = date("w", $datefrom);
            $days_remainder = floor($days_difference % 7);
            $odd_days = $first_day+$days_remainder; // Do we have a Saturday or Sunday in the remainder?

            if ($odd_days > 7) { // Sunday
                $days_remainder--;
            }

            if ($odd_days > 6) { // Saturday
                $days_remainder--;
            }

            $datediff = ($weeks_difference * 5)+$days_remainder;
            break;

        case "ww": // Number of full weeks
            $datediff = floor($difference / 604800);
            break;

        case "h": // Number of full hours
            $datediff = floor($difference / 3600);
            break;

        case "n": // Number of full minutes
            $datediff = floor($difference / 60);
            break;

        default: // Number of full seconds (default)
            $datediff = $difference;
            break;
        }

        return $datediff;
    }

    /**
     * Build directory
     *
     * @param string $directory directory name
     */
    public function makeDir($directory)
    {
        if (is_dir($directory) === false) {
            mkdir($directory, 0777);
        }
    }

    /**
     * Send email
     */
    public function sendMail()
    {
        $transport = Swift_MailTransport::newInstance();
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance($this->emailSubject)
          ->setFrom(array($this->emailFrom => $this->emailFromName))
          ->setTo($this->emailTo)
          ->setBody($this->emailContent);
        $result = $mailer->send($message);

        return $result;
    }

    /**
     * Cut date part
     *
     * @param string $date date string
     * @return string
     */
    public function dateOnly($date)
    {
        return date('Y-m-d', strtotime($date));
    }

    /**
     * Cut string
     *
     * @param string $string string
     * @param integer $length string length
     * @param string $symbol replace string
     */
    public function cutStr($string, $length, $symbol = '...')
    {
        mb_internal_encoding($this->charset);
        $string = trim(strip_tags($string));

        if (mb_strlen($string) > $length) {
            return mb_substr($string, 0, $length) . $symbol;
        } else {
            return $string;
        }
    }

    /**
     * Now
     *
     * @return string
     */
    public function retNow()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * IP
     *
     * @return string
     */
    public function retIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Current uri
     *
     * @param string $url combine url if assigned
     * @return string
     */
    public function retUri($url = null)
    {
        if ($url === null) {
            return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            return $this->retUriPath() . $url;
        }
    }

    /**
     * Full url directory path
     *
     * @return string
     */
    public function retUriPath()
    {
        $path = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);

        if ($path === '/') {
            $path = '';
        }

        return 'http://' . $_SERVER['HTTP_HOST'] . $path . '/';
    }

    /**
     * Convert data to smarty option format
     * $data - 資料
     * $value - 值
     * $text - 名稱
     * $name - 第一項標題
     */
    public function retSmartyOption($data = null, $value, $text, $select = null)
    {
        $result = array();

        if ($data !== null) {
            if ($select === null) {
                $select = $this->_langSelect;
            }

            $result[''] = $select;

            foreach ($data as $v) {
                $result[$v[$value]] = $v[$text];
            }
        }

        return $result;
    }

    /**
     * Show message
     *
     * @param string $message message to be show
     */
    public function showMsg($message = null)
    {
        if ($message !== null) {
            echo '<div style="border: 1px solid orange; text-align: center; background: #E1FDE3; padding: 4px; font-size: 14px; margin: 2px;">' . $message . '</div>';
        }
    }



    /**
     * Random password
     *
     * @param integer length
     * @return string
     */
    public function randomPwd($length = 8)
    {
        $result = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, 35)];
        }

        return $result;
    }

    /**
     * Get content by cURL
     *
     * @param string $soruce source
     * @param string $type (post|get)
     * @return string
     */
    public function curl($source = null, $type = 'get', $fields = array())
    {
        $result = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (strtolower($type) === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);

            if (count($fields) !== 0) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            }
        }

        $result = curl_exec($ch);

        if ($result === false) {
            $result = curl_error($ch);
        }

        curl_close($ch);

        return $result;
    }

    /**
     * Split field as individual array
     *
     * @param array $items array item
     * @param array $fieldName field name
     * @return array
     */
    public function lists($items, $fieldName)
    {
        $result = array_map(function ($item) use ($fieldName) {
            return $item[$fieldName];
        }, $items);

        return $result;
    }

    /**
     * Force download file
     *
     * @param string $fileName file name you want
     * @param string $path absolute path of the file
     */
    public function download($fileName, $path)
    {
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: Binary');
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        echo file_get_contents($path);
    }

    /**
     * Check if value exists
     *
     * @param string $name
     * @return boolean
     */
    public function hasValue($field)
    {
        if (isset($field) === true && $field !== '') {
            return true;
        }

        return false;
    }
}
