<?php


namespace Phiber\Validator;

class validator
{

    public static $callbacks = array();
    public $subject = array();
    protected $candidate;
    protected $key;
    protected $errors;
    protected $error_msg;

    public function __construct(array $subject = array(), $msg = null)
    {
        static::addDefaultValidators();
        if (!is_array($subject)) {
            $subject = array($subject);
        }
        $this->subject = $subject;
        $this->error_msg = $msg;
    }

    public function is($key, $error_msg = null)
    {
        if (array_key_exists($key, $this->subject)) {
            $this->fill($this->subject[$key], $error_msg);
            return $this;
        }
        $this->fill($key, $error_msg);

        return $this;
    }

    public static function addValidator($method, $callback)
    {
        static::$callbacks[strtolower($method)] = $callback;
    }

    public function hasErrors()
    {
        return count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function valid()
    {
        if ($this->hasErrors()) {
            return false;
        }
        return true;
    }

    public function __call($method, $args)
    {
        if (!$this->error_msg = end($args)) {
            $this->error_msg = 'Not valid!';
        }
        $validator_name = strtolower($method);

        if (!array_key_exists($validator_name, static::$callbacks)) {
            throw new \BadMethodCallException("Unknown validator method $method()");
        }

        $validator = static::$callbacks[$validator_name];
        array_unshift($args, $this->candidate);
        $result = (bool)call_user_func_array($validator, $args);


        if ($result === false) {
            $this->errors[$this->key][] = $this->error_msg;
        }

        return $this;
    }

    protected function fill($key, $error_msg)
    {
        if (null === $error_msg) {
            $error_msg = 'Not valid!';
        }
        $this->candidate = $key;
        $this->key = $key;
        $this->error_msg = $error_msg;
    }
    protected static function addDefaultValidators()
    {
        static::$callbacks['null'] = function ($str) {
            return $str === null || $str === '';
        };
        static::$callbacks['max'] = function ($str, $max) {
            $len = strlen($str);
            return $len <= $max;
        };
        static::$callbacks['min'] = function ($str, $min) {
            $len = strlen($str);
            return $len >= $min;
        };
        static::$callbacks['int'] = function ($str) {
            return (string)$str === ((string)(int)$str);
        };
        static::$callbacks['float'] = function ($str) {
            return (string)$str === ((string)(float)$str);
        };
        static::$callbacks['email'] = function ($str) {
            return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
        };
        static::$callbacks['url'] = function ($str) {
            return filter_var($str, FILTER_VALIDATE_URL) !== false;
        };
        static::$callbacks['ip'] = function ($str) {
            return filter_var($str, FILTER_VALIDATE_IP) !== false;
        };
        static::$callbacks['alnum'] = function ($str) {
            return ctype_alnum($str);
        };
        static::$callbacks['alpha'] = function ($str) {
            return ctype_alpha($str);
        };
        static::$callbacks['contains'] = function ($str, $needle) {
            return strpos($str, $needle) !== false;
        };
        static::$callbacks['sameas'] = function ($str, $needle) {
            return (strcmp($str, $needle) === 0);
        };
        static::$callbacks['regex'] = function ($str, $pattern) {
            return preg_match($pattern, $str);
        };
        static::$callbacks['chars'] = function ($str, $chars) {
            return preg_match("/[$chars]+/i", $str);
        };
    }

}