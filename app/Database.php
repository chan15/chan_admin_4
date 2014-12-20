<?php namespace Chan;

use \PDO;

class Database
{
    public $makeRecordCount    = true;
    public $recordCount        = 0;
    public $totalRecordCount   = 0;
    public $fieldArray         = array();
    public $valueArray         = array();
    public $sqlErrorMessage    = '';
    public $table              = '';
    public $pk                 = '';
    public $pkValue            = '';
    private $dbh               = null;
    private $dbhRead           = null;
    private $dbhWrite          = null;
    private $stmt              = null;
    public $deleteFiles        = array();
    public $page               = 0;
    public $totalPages         = 0;
    private $paramType         = array();
    public $_langPrevPage      = '上一頁';
    public $_langFirstPage     = '第一頁';
    public $_langNextPage      = '下一頁';
    public $_langLastPage      = '最後頁';
    public $_langInput         = '請填寫';
    public $_langDuplicate     = '重複';
    public $_langFormatInvalid = '格式錯誤';
    public $_langOverLength    = '超過字數';
    public $_langUrlError      = '連結方式錯誤';
    public $_langSelect        = '請選擇';
    public $_langFileNotExist  = '檔案不存在';

    public function __construct($configName = 'default'){
        $this->paramType = array(
            'bool' => PDO::PARAM_BOOL,
            'null' => PDO::PARAM_NULL,
            'int'  => PDO::PARAM_INT,
            'str'  => PDO::PARAM_STR,
            'date' => PDO::PARAM_STR,
        );

        $config = include dirname(__DIR__) . '/config/database.php';
        $config = $config[$configName];
        $dsnRead = null;
        $dsnWrite = null;
        $dsn = null;
        $host = $config['host'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $options = array(
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        switch ($config['driver']) {
            case 'mysql':
                if (is_array($host) === true) {
                    $dsnRead = 'mysql:host=' . $host['read'] . ';dbname=' . $database . ';charset=utf8';
                    $dsnWrite = 'mysql:host=' . $host['write'] . ';dbname=' . $database . ';charset=utf8';
                } else {
                    $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8';
                }
                break;
            case 'sqlite':
                $dsn = 'sqlite:' . $database;
                break;
        }

        try {
            if ($dsn === null) {
                $this->dbhRead = new PDO($dsnRead, $username, $password, $options);
                $this->dbhWrite = new PDO($dsnWrite, $username, $password, $options);
            } else {
                switch ($config['driver']) {
                    case 'mysql':
                        $this->dbh = new PDO($dsn, $username, $password, $options);
                        break;
                    case 'sqlite':
                        $this->dbh = new PDO($dsn);
                        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        break;
                }
            }
        } catch (PDOException $e) {
            echo '<pre>'; var_dump($e->getMessage()); echo '</pre>'; die();
        }
    }

    /**
     * Get row amount
     *
     * @return integer
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get last insert id
     *
     * @return integer
     */
    public function lastInsertId()
    {
        if ($this->dbh === null) {
            return $this->dbhWrite->lastInsertId();
        } else {
            return $this->dbh->lastInsertId();
        }
    }

    /**
     * Dump query params
     *
     * @return string
     */
    public function debugDumpParams(){
        return $this->stmt->debugDumpParams();
    }

    /**
     * PDO begin transaction
     *
     * @return PDO object
     */
    public function beginTransaction()
    {
        if ($this->dbh !== null) {
            // Single database
            return $this->dbh->beginTransaction();
        } else {
            // Double database
            return $this->dbhWrite->beginTransaction();
        }
    }

    /**
     * PDO commit transaction
     *
     * @return PDO object
     */
    public function commitTransaction()
    {
        if ($this->dbh !== null) {
            // Single database
            return $this->dbh->commit();
        } else {
            // Double database
            return $this->dbhWrite->commit();
        }
    }

    /**
     * PDO rollback transaction
     *
     * @return PDO object
     */
    public function rollBackTransaction()
    {
        if ($this->dbh !== null) {
            // Single database
            return $this->dbh->rollBack();
        } else {
            // Double database
            return $this->dbhWrite->rollBack();
        }
    }

    /**
     * Add table field
     *
     * @param mixed $field field
     * @param mixed $value field value
     * @param string $type field type
     */
    public function addField($field, $value, $type = 'str')
    {
        $this->fieldArray[] = '`' . $field . '`';
        $this->valueArray[] = array('type' => $type, 'value' => $value);
    }

    /**
     * Bind PDO value
     *
     * $param string $value
     * $param string $type (bool|null|int|str|date)
     * @return void
     */
    public function addValue($value, $type = 'str')
    {
        $this->valueArray[] = array('type' => $type, 'value' => $value);
    }

    /**
     * Get file name from database
     *
     * @param string $field field name
     * @return string
     **/
    public function getFileName($field)
    {
        $sql = sprintf("SELECT `%s` FROM `%s` WHERE `%s` = %s",
                $field,
                $this->table,
                $this->pk,
                $this->pkValue);
        $row = $this->myOneRow($sql);

        return $row[$field];
    }

    /**
     * Delte file from database
     *
     * @param string $path file path
     * @return void
     **/
    public function dataFileDelete($path = null)
    {
        if (count($this->deleteFiles) > 0) {
            if (is_dir($path) === true) {
                foreach ($this->deleteFiles as $fileName) {
                    File::delete($path . $fileName);
                    $fileDelHead = explode('.', $fileName);
                    $thumbDir = $path . 'thumbnails/';
                    $handle = @opendir($thumbDir);

                    while ($file = readdir($handle)) {
                        if ($file !== '.' && $file !== '..') {
                            $fileDel = explode('_', $file);

                            if ($fileDelHead[0] === $fileDel[0]) {
                                File::delete($thumbDir . $file);
                            }
                        }
                    }

                    closedir($handle);
                }
            }
        }
    }

    /**
     * Insert data
     *
     * @return boolean
     */
    public function dataInsert()
    {
        $sql = sprintf("INSERT INTO `%s` (%s) VALUES(%s)",
            $this->table,
            implode(', ', $this->fieldArray),
            implode(', ', array_fill(0, count($this->fieldArray), '?')));
        $result = $this->prepare($sql);

        if (count($this->valueArray) > 0) {
            $index = 1;

            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->paramType[$item['type']]);
                $index++;
            }
        }

        $this->clearFields();

        if ($result->execute() === false) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        return true;
    }

    /**
     * Update data
     *
     * @param string $where defined where condition
     * @return boolean
     */
    public function dataUpdate($where = null)
    {
        $sqlString = array();
        $index = 1;

        foreach ($this->fieldArray as $k => $v) {
            $sqlString[] = $v . ' = ?';
        }

        if ($where === null) {
            $condition = sprintf("`%s` = ?",
                $this->pk);
        } else {
            $condition = $where;
        }

        $sql = sprintf("UPDATE `%s` SET %s WHERE %s",
            $this->table,
            implode(', ', $sqlString),
            $condition);
        $result = $this->prepare($sql);

        if (count($this->valueArray) > 0) {
            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->paramType[$item['type']]);
                $index++;
            }
        }

        if ($where === null) {
            $result->bindValue($index, $this->pkValue, $this->paramType['int']);
        }

        $this->pk = '';
        $this->pkValue = '';
        $this->clearFields();

        if ($result->execute() === false) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        return true;
    }

    /**
     * Insert or update data
     *
     * @param string $where defined where condiion
     * @return boolean
     */
    public function save($where = null)
    {
        if ('' === $this->pkValue) {
            return $this->dataInsert();
        } else {
            return $this->dataUpdate($where);
        }
    }

    /**
     * Delete data
     *
     * @param string $where defined where condition
     * @return boolean
     */
    public function delete($where = null)
    {
        $index = 1;

        if ($where === null) {
            $sql = sprintf("DELETE FROM `%s` WHERE `%s` = ?",
                $this->table,
                $this->pk);
        } else {
            $sql = sprintf("DELETE FROM `%s` WHERE %s",
                $this->table,
                $where);
        }

        $result = $this->prepare($sql);

        if (count($this->valueArray) > 0) {
            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->paramType[$item['type']]);
                $index++;
            }
        }

        if ($where === null) {
            $result->bindValue($index, $this->pkValue, $this->paramType['int']);
        }

        $this->clearFields();

        if ($result->execute() === false) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        return true;
    }

    /**
     * Get one data
     *
     * @param string $sql sql statement
     * @return data|NULL
     */
    public function myOneRow($sql)
    {
        $result = $this->myRow($sql);

        if ($result !== null) {
            $result = current($result);
        }

        return $result;
    }

    /**
     * PDO prepare
     *
     * @param string $sql
     * @return PDO object
     */
    public function prepare($sql)
    {
        $result = null;

        if ($this->dbh !== null) {
            // Single database
            $result = $this->dbh->prepare($sql);
        } else {
            // Double Database
            if (preg_match('/^select /i', $sql) > 0) {
                $result = $this->dbhRead->prepare($sql);
            } else {
                $result = $this->dbhWrite->prepare($sql);
            }
        }

        return $result;
    }

    /**
     * Get data
     *
     * @param string $sql sql statement
     * @return data|null
     */
    public function myRow($sql = null)
    {
        $result = $this->prepare($sql);
        $index = 1;

        if (count($this->valueArray) > 0) {
            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->paramType[$item['type']]);
                $index++;
            }
        }

        if ($result->execute() === false) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        $results = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->recordCount = count($results);

        if ($this->makeRecordCount === true) {
            $this->totalRecordCount = $this->recordCount;
        }

        $this->clearFields();

        if ($this->recordCount === 0) {
            return null;
        }

        return $results;
    }

    /**
     * Get data by limit
     *
     * @param string $sql sql statement
     * @param integer $max data per page
     * @return data
     */
    public function myRowList($sql, $max = 10)
    {
        $this->page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        $startRow = $this->page * $max;
        $tempValue = $this->valueArray;
        $row = $this->myRow($sql);

        if ($row === null) {
            return null;
        }

        $this->totalRecordCount = count($row);
        $this->totalPages = ceil($this->totalRecordCount / $max) - 1;
        $this->valueArray = $tempValue;
        $sqlPages = sprintf("%s LIMIT %d, %d", $sql, $startRow, $max);
        $this->makeRecordCount = false;
        $row = $this->myRow($sqlPages);

        return $row;
    }

    /**
     * Combine url param
     *
     * @param string $string combine string
     * @return string
     */
    public function combineQueryString($string)
    {
        $result = '';

        if (empty($_SERVER['QUERY_STRING']) === false) {
            $params = explode('&', $_SERVER['QUERY_STRING']);
            $newParams = array();

            foreach ($params as $param) {
               if (stristr($param, $string) === false) {
                   array_push($newParams, $param);
               }
            }

            if (count($newParams) !== 0) {
                $result = '&' . htmlentities(implode('&', $newParams));
            }
        }

        return $result;
    }

    /**
     * Default pager
     *
     * @param integer $limit data per page
     * @return string
     */
    public function pager($limit = 5)
    {
        $sep = '&nbsp;';
        $result = '';
        $result .= $this->pageString('prev', null, 'prev') . $sep;
        $result .= $this->pageNumber($limit) . $sep;
        $result .= $this->pageString('next', null, 'next') . $sep;

        return $result;
    }

    /**
     * Bootstrap pager
     *
     * @param integer $limit data per page
     * @return string
     */
    public function bootstrapPager($limit = 6)
    {
        $currentPage = $_SERVER["PHP_SELF"];
        $result = '';
        $result .= '<ul class="pagination">';
        $limitLinksEndCount = $limit;
        $temp = intval(($this->page + 1));
        $startLink = intval((max(1, $temp - intval($limitLinksEndCount / 2))));
        $temp = intval(($startLink + $limitLinksEndCount - 1));
        $endLink = min($temp, $this->totalPages + 1);

        // Prev page
        if ($this->page > 0) {
            $result .= sprintf('<li><a href="%s?page=%d%s">«</a></li>',
                $currentPage,
                max(0, intval($this->page - 1)),
                $this->combineQueryString('page'));
        } else {
            $result .= sprintf('<li class="disabled"><a>«</a></li>',
                $currentPage,
                max(0, intval($this->page - 1)),
                $this->combineQueryString('page'));
        }

        if ($endLink !== $temp) {
            $startLink = max(1, intval(($endLink-$limitLinksEndCount + 1)));
        }

        for ($i = $startLink; $i <= $endLink; $i++) {
            $limitPageEndCount = $i - 1;
            if ($this->page !== $limitPageEndCount) {
                $result .= sprintf('<li><a href="%s?page=%d%s">%s</a></li>',
                    $currentPage,
                    $limitPageEndCount,
                    $this->combineQueryString('page'),
                    $i);
            } else {
                $result .= '<li class="disabled"><a>' . $i . '</a></li>';
            }
        }

        // Next page
        if ($this->page < $this->totalPages) {
            $result .= sprintf('<li><a href="%s?page=%d%s">»</a></li>',
                $currentPage,
                min($this->totalPages, intval($this->page + 1)),
                $this->combineQueryString('page'));
        } else {
            $result .= sprintf('<li class="disabled"><a>»</a></li>',
                $currentPage,
                min($this->totalPages, intval($this->page + 1)),
                $this->combineQueryString('page'));
        }

        $result .= "</ul>";

        return $result;
    }

    /**
     * Prev or nex page
     *
     * @param string $method prev or next
     * @param string $string display word
     * @param string $class css class name
     * @return string
     */
    public function pageString($method, $string = null, $class = '')
    {
        $currentPage = $_SERVER["PHP_SELF"];
        $result = '';

        switch ($method) {
            case 'first':
                if ($this->page > 0) {
                    if ($string === null) {
                        $string = $this->_langFirstPage;
                    }
                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        0,
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }

                break;
            case 'prev':
                if ($this->page > 0) {
                    if ($string === null) {
                        $string = $this->_langPrevPage;
                    }
                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        max(0, $this->page - 1),
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }

                break;
            case 'next':
                if ($this->page < $this->totalPages) {
                    if ($string === null) {
                        $string = $this->_langNextPage;
                    }

                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        min($this->totalPages, $this->page + 1),
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }
                break;
            case 'last':
                if ($this->page < $this->totalPages) {
                    if ($string === null) {
                        $string = $this->_langLastPage;
                    }

                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        $this->totalPages,
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }
                break;
        }

        return $result;
    }

    /**
     * Page number
     *
     * @param integer $limit data per page
     * @param string $set seperation
     * @return string
     */
    public function pageNumber($limit = 5, $sep = '&nbsp;')
    {
        $result = '';
        $currentPage = $_SERVER["PHP_SELF"];
        $limitLinksEndCount = $limit;
        $temp = intval($this->page + 1);
        $startLink = max(1, $temp - intval($limitLinksEndCount / 2));
        $temp = intval($startLink + $limitLinksEndCount - 1);
        $endLink = min($temp, $this->totalPages + 1);

        if ($endLink !== $temp) {
            $startLink = max(1, $endLink - $limitLinksEndCount + 1);
        }

        for ($i = $startLink; $i <= $endLink; $i++) {
            $limitPageEndCount = intval($i - 1);

            if ($limitPageEndCount !== $this->page) {
                $result .= sprintf('<a href="' . "%s?page=%d%s", $currentPage, $limitPageEndCount, $this->combineQueryString('page') . '">');
                $result .= $i . '</a>';
            } else {
                $result .= '<strong>' . $i . '</strong>';
            }

            if ($i !== $endLink) {
                $result .= $sep;
            }
        }

        return $result;
    }

    /**
     * Get max sort
     *
     * @param string $sort field name
     * @param string $where where condition
     * @return integer
     */
    public function maxSort($sort = 'sort', $where = '1 = 1')
    {
        $sql = sprintf('SELECT MAX(`%s`) as `maxSort` FROM `%s` WHERE %s',
            $sort,
            $this->table,
            $where
        );

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($row === false) ? 1 : (intval($row['maxSort']) + 1);
    }

     /**
     * Execute sql
     * @param string $sql SQL statement
     *
     * @return boolean
     */
    public function sqlExecute($sql = null)
    {
        $result = $this->prepare($sql);

        if ($result->execute() === false) {
            $errorMessage = $this->dbh->errorInfo();
            $this->sqlErrorMessage = $errorMessage[2];

            return false;
        }

        $this->clearFields();

        return true;
    }

    /**
     * Clear fields
     */
    public function clearFields()
    {
        $this->fieldArray = array();
        $this->valueArray = array();
    }

    public function __destruct()
    {
        // Destroy the connection
        $this->dbh = null;
        $this->dbhRead = null;
        $this->dbhWrite = null;
    }
}
