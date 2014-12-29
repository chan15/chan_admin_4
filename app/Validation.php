<?php namespace Chan;

class Validation
{
    public $pass = true;
    public $message = array();
    public $translate = null;
    public $lang = null;
    protected $inputs = null;

    public function __construct($lang = 'tw')
    {
        mb_internal_encoding('utf-8');

        $this->lang = include dirname(__DIR__) . '/lang/' . $lang . '/validation.php';
    }

    /**
     * Start validate
     *
     * @param array $rules
     */
    public function check($inputs = null, $rules = array(), $translate = null)
    {
        if ($translate !== null) {
            $this->translate = $translate;
        }

        if ($inputs === null) {
            $this->inputs = $_REQUEST;
        } else {
            $this->inputs = $inputs;
        }

        if (count($rules) > 0) {
            foreach ($rules as $name => $rule) {
                foreach (explode('|', $rule) as $func) {
                    $param = '';

                    if (stristr($func, ':') !== false) {
                        $funcSplit = explode(':', $func);
                        $funcName = $funcSplit[0];
                        $param = $funcSplit[1];
                    } else {
                        $funcName = $func;
                    }

                    if (method_exists(__CLASS__, $funcName) === true) {
                        $this->{$funcName}($name, $param);
                    } else {
                        die("rule ${funcName} is not exists");
                    }
                }
            }
        }

        return $this->pass;
    }

    /**
     * Validate file
     *
     * @param string $name
     * @return void
     */
    public function file($name = null)
    {
        if (empty($_FILES[$name]['name']) === true) {
            $this->pass = false;
            $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__];
        }
    }

    /**
     * Validate required
     *
     * @param string $name
     * @return void
     */
    public function required($name = null)
    {
        if ($this->hasValue($name) === false) {
            $this->pass = false;
            $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__];
        }
    }

    /**
     * Validate string length
     *
     * @param string $name
     * @param integer $param
     * @return void
     */
    public function length($name = null, $param = null)
    {
        if ($this->hasValue($name) !== false) {
            if (mb_strlen($this->inputs[$name]) !== intval($param)) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__] . $param;
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validate string max length
     *
     * @param string $name
     * @param integer $param
     * @return void
     */
    public function maxLength($name = null, $param = null)
    {
        if ($this->hasValue($name) !== false) {
            if (mb_strlen($this->inputs[$name]) > intval($param)) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__] . $param;
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validate string min length
     *
     * @param string $name
     * @param integer $param
     * @return void
     */
    public function minLength($name = null, $param = null)
    {
        if ($this->hasValue($name) !== false) {
            if (mb_strlen($this->inputs[$name]) < intval($param)) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__] . $param;
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validate integer format
     *
     * @param string $name
     * @return void
     */
    public function integer($name = null)
    {
        if ($this->hasValue($name) !== false) {
            if (filter_var($this->inputs[$name], FILTER_VALIDATE_INT) === false) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__];
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validte boolean format
     *
     * @param string $name
     * @return void
     */
    public function bool($name = null)
    {
        if ($this->hasValue($name) !== false) {
            if (filter_var($this->inputs[$name], FILTER_VALIDATE_BOOLEAN) === false) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__];
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validate url format
     *
     * @param string $name
     * @return void
     */
    public function url($name = null)
    {
        if ($this->hasValue($name) !== false) {
            if (filter_var($this->inputs[$name], FILTER_VALIDATE_URL) === false) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__];
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validate max value
     *
     * @param string $name
     * @param integer $param
     * @return void
     */
    public function max($name = null, $param = null)
    {
        if ($this->hasValue($name) !== false) {
            if (intval($this->inputs[$name]) > intval($param)) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__] . $param;
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validate min value
     *
     * @param string $name
     * @param integer $param
     * @return void
     */
    public function min($name = null, $param = null)
    {
        if ($this->hasValue($name) !== false) {
            if (intval($this->inputs[$name]) < intval($param)) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__] . $param;
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Validate unique value
     *
     * @param string $name config.table_name.field_name
     * @param integer $param
     * @return void
     */
    public function unique($name = null, $param = null)
    {
        if ($this->hasValue($name) !== false) {
            list($config, $table, $field) = explode('.', $param);

            $db = new Database($config);
            $db->table = $table;
            $query = sprintf("SELECT *
                FROM `%s`
                WHERE `%s` = ?",
                $table,
                $field);
            $db->addValue($this->inputs[$name]);
            $result = $db->myRow($query);

            if ($result !== null) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__];
            }
        }
    }

    /**
     * Validation email format
     *
     * @param string $name
     * @return void
     */
    public function email($name = null)
    {
        if ($this->hasValue($name) !== false) {
            if (filter_var($this->inputs[$name], FILTER_VALIDATE_EMAIL) === false) {
                $this->pass = false;
                $this->message[$name][__FUNCTION__] = $this->translate($name) . $this->lang[__FUNCTION__];
            }
        } else {
            $this->pass = false;
        }
    }

    /**
     * Translate filed name
     *
     * @param string $name
     * @return string
     */
    public function translate($name = null) {
        if ($this->translate !== null) {
            if (isset($this->translate[$name]) === true) {
                return $this->translate[$name];
            }

        }

        return $name;
    }

    /**
     * Return first error
     *
     * @param string $name
     * @return string
     */
    public function first($name = null)
    {
        if ($name === null) {
            return current(current($this->message));
        } else {
            if (isset($this->message[$name]) === true) {
                return current($this->message[$name]);
            } else {
                return $name . ' is not exists';
            }
        }
    }

    /**
     * Return all errors as array
     *
     * @return array
     */
    public function all()
    {
        $string = array();

        foreach ($this->message as $name) {
            foreach ($name as $value) {
                $string[] = $value;
            }
        }

        return $string;
    }

    /**
     * Check if value exists
     *
     * @param string $name
     * @return boolean
     */
    private function hasValue($name = null)
    {
        if (isset($this->inputs[$name]) === true && $this->inputs[$name] !== '') {
            return true;
        }

        return false;
    }
}
