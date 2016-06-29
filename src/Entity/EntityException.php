<?php

namespace Purpose\Entity;

class EntityException extends BaseException
{
    public function __construct($key, $values=null)
    {
        parent::__construct('');

        $this->setKey($key);

        if(is_array($values))
        {
            $this->values($values);
        }

        $this->setMessageByKey();
    }

    public $values = [];

    public function setMessageByKey()
    {
        if(!array_key_exists($this->_key, self::$keyMessages))

            return $this->setMessage("Exception message('{$this->key}') not found");

        $str = self::$keyMessages[$this->_key];

        $values = $this->values();

        foreach($values as $key=>$value) $str = str_replace('{'.$key.'}', $value, $str);

        $this->setMessage($str);
    }

    public static $keyMessages = [
        'link-not-allowed'      => "The linkage of entities '{self}' and '{entity}' is not allowed.",
        'merge-null-data'       => "Data from entity->merge() must be array or object. Null given.",
        'property-not-allowed'  => "Property {entity}->'{key}' is not allowed on this entity.",
    ];
}
