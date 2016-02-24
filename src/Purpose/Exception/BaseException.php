<?php

namespace App\Exceptions;

/**
  * Classe base para as exceptions da aplicação
  */

class BaseException extends \Exception
{

    public $_inner_exception         ;

    public $_method              = "";

    public $_class               = "";

    public $_date                = "";

    public $_url                 = "";

    public $_type                = "undefined";

    public $_key                 = '';

    public $_save                = true;

    public $_registry            = array();


    public $was_saved            = false;

    public $user_ip              = "";

    public $remote_port          = "";

    public $request_method       = "";

    public $http_user_agent      = "";

    public $server_admin         = "";


    public function __construct($msg = '', $key = 0, Exception $inner_exception = null)
    {

        $this->_inner_exception = $inner_exception;

        parent::__construct($msg, 0, $inner_exception);

        $trace = debug_backtrace();

        $this->_key = $key;

        $this->setRegistryFromTrace($trace);

        if(isset($trace[1]["function"])) $this->_method = $trace[1]["function"];

        if(isset($trace[1]["class"])) $this->_class = $trace[1]["class"];

        if(isset($trace[1]["type"])) $this->_type = $trace[1]["type"];

        //date_default_timezone_set('America/Sao_paulo');

        $this->_date = date('Y-m-d H:i:s');

        $this->_url = $_SERVER['HTTP_HOST']."".$_SERVER['REQUEST_URI'];

        $this->user_ip = (!empty($_SERVER['HTTP_CLIENT_IP'])) ? $_SERVER['HTTP_CLIENTuser_ip']
            :(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR']
            :$_SERVER['REMOTE_ADDR'];

        if(isset($_SERVER["REMOTE_PORT"]))
            $this->remote_port      =  $_SERVER["REMOTE_PORT"];

        if(isset($_SERVER["REQUEST_METHOD"]))
            $this->request_method   =  $_SERVER["REQUEST_METHOD"];

        if(isset($_SERVER["HTTP_USER_AGENT"]))
            $this->http_user_agent  =  $_SERVER["HTTP_USER_AGENT"];

        if(isset($_SERVER["SERVER_ADMIN"]))
            $this->server_admin     =  $_SERVER["SERVER_ADMIN"];

        if($msg instanceof Exception and !$msg instanceof Alpha_exception)

            $this->incorpore($msg);

        $this->construct();
    }

    public function construct() {}

    public function getRegistry($n=null)
    {
        if($n===null) return $this->_registry;

        if(isset($this->_registry[$n]))

            return $this->_registry[$n];
    }

    public function clearRegistry($h=null)
    {
        $this->_registry = []; return $this;
    }

    public function addRegistry($i=null)
    {
        if(!is_array($i)) return;

        $type = isset($i["type"])? $i["type"] : "#";

        $type = $type=="::"?"static":$type=="->"?"dynamic":"undefined";

        $this->_registry[] = [
            "file"=>(isset($i["file"])?$i["file"]:""),
            "line"=>(isset($i["line"])?$i["line"]:""),
            "class"=>(isset($i["class"])?$i["class"]:""),
            "method"=>(isset($i["function"])?$i["function"]:""),
            "type"=>(isset($i["type"])?$i["type"]:"")
            ];

        return $this;
    }

    public function setMessage($m)
    {
        $this->message = $m; return $this;
    }

    public function setString($str)
    {
        $this->string = $str; return $this;
    }

    public function setLine($n)
    {
        $this->line = $n;
        return $this;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    public function setClass($class)
    {
        $this->_class = $class;
        return $this;
    }

    public function hasChild()
    {
        return $this->_inner_exception ? true : false;
    }

    public function getChild()
    {
        return $this->_inner_exception;
    }

    public function getDate()
    {
        return $this->_date;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getClass()
    {
        return $this->_class;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setType($value)
    {
        $this->_type = $value; return $this;
    }

    public function setKey($key)
    {
        $this->_key = $key; return $this;
    }

    public function setCode($code)
    {
        $this->code = $code; return $this;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function asParent()
    {
        array_shift($this->_registry);

        $r = $this->lastRegistry();

        $this->line = isset($r["line"]) ? $r["line"] : "---";
        $this->file = isset($r["file"]) ? $r["file"] : "---";

        if(count($this->_registry)<=1)
        {
            $this->_method = "";
            $this->_class = "";
            $this->_type = "";
        }
        else
        {
            $c = count($this->_registry)-2;

            $list = array_reverse($this->_registry);

            $r = isset($list[$c])?$list[$c]:null;

            if($r!=null)
            {
                $this->_method = $r["method"];
                $this->_class = $r["class"];
                $this->_type = $r["type"];
            }
            else
            {
                $this->_method = "";
                $this->_class = "";
                $this->_type = "";
            }
        }

        return $this;
    }

    public function lastRegistry()
    {
        return isset($this->_registry[0])?$this->_registry[0]:null;
    }

    public function removeLastRegistry()
    {
        return array_shift($this->_registry);
    }

    public function setRegistryFromTrace($trace=array())
    {
        $this->_registry = [];

        if(!$trace) return;

        foreach($trace as $k=>$i) $this->addRegistry($i);
    }

    public function incorpore($exception)
    {
        $this->setMessage($exception->getMessage());
        $this->setFile($exception->getFile());
        $this->setLine($exception->getLine());
        $this->setCode($exception->getCode());

        $trace = $exception->getTrace();

        $this->setRegistryFromTrace($trace);

        if(!isset($trace[0])) return $this;

        $last = $trace[0];

        $this->setMethod(isset($last["function"])?$last["function"]:"");

        $this->setClass(isset($last["class"])?$last["class"]:"");

        return $this;
    }

    //------------------------------------------------

    public function toArray()
    {
        return [
                "message"           => $this->getMessage(),
                "file"              => $this->getFile(),
                "line"              => $this->getLine(),
                "method"            => $this->_method,
                "class"             => $this->_class,
                "type"              => $this->getType(),
                "url"               => $this->_url,
                "date"              => $this->_date,
                "code"              => $this->getKey(),
                "user_ip"           => $this->user_ip,
                "server_admin"      => $this->server_admin,
                "remote_port"       => $this->remote_port,
                "request_method"    => $this->request_method,
                "http_user_agent"   => $this->http_user_agent,
                "registry"          => $this->_registry,
            ];
    }

    public function __tostring()
    {
        return json_encode($this->toArray());
    }

    public function serialize()
    {
        return json_encode($this->toArray());
    }

    public function is($code)
    {
        return $this->_key == $code;
    }

    public function load($arr)
    {

        $this->file =       isset($arr->file)       ?$arr->file     :0 ;
        $this->line =       isset($arr->line)       ?$arr->line     :"";
        $this->message =    isset($arr->message)    ?$arr->message  :"";
        $this->_key =       isset($arr->code)       ?$arr->code     :0 ;

        $this->_method =    isset($arr->method)     ?$arr->method   :"";
        $this->_class =     isset($arr->class)      ?$arr->class    :"";
        $this->_date =      isset($arr->date)       ?$arr->date     :"";
        $this->_url =       isset($arr->url)        ?$arr->url      :"";
        $this->_type =      isset($arr->type)       ?$arr->type     :"";
        $this->user_ip =    isset($arr->user_ip)    ?$arr->user_ip  :"";

        if(isset($arr->registry))
        {
            $this->_registry = array();

            if(is_string($arr->registry))
                $arr->registry = json_decode($arr->registry);

            if($arr->registry!=null)
            {
                foreach ($arr->registry as $key => $reg) $this->_registry[] = (array)$reg;
            }
        }
    }

    private $data = [];

    public function set($key_or_data, $val=null)
    {
        if(is_array($key_or_data))

            $this->data = array_merge($this->data, $key_or_data);
        else
            $this->data[$key_or_data] = $val;

        return $this;
    }

    public function get($name) { return isset($this->data[$name])?$this->data[$name]:null; }

    public function values(array $array=null)
    {
        if(!$array) return $this->data;

        $this->data = array_merge($this->data, $array);

        return $this;
    }

    //=============================================================

    //  METODOS ESTATICOS

    public static $listeners = array();

    public static function bind($key, $fn)
    {
        if(is_callable($fn)) self::$listeners[$key] = $fn;
    }

    public static function trigger($key, $exception)
    {
        foreach(self::$listeners as $_key=>$fn)
        {
            if($_key!=$key) continue;

            $fn($exception); break;
        }
    }

    public static function throwAsParent()
    {
        $self_class_name = get_called_class();

        $exception_class = (new \ReflectionClass($self_class_name))->newInstanceArgs(func_get_args());

        throw $exception_class->asParent()->asParent();
    }

}
