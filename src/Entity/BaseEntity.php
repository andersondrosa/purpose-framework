<?php

namespace Purpose\Entity;

class BaseEntity
{

    public function __construct($arg=null)
    {
        if(is_numeric($arg))
        {
            return $this->set($this->primaryKey(), $arg);
        }

        if(gettype($arg)=="array") $this->merge($arg, true);

        return $this;
    }

    public function __get($key)
    {
        throw (new EntityException('property-not-allowed', ['key'=>$key, 'entity'=>get_called_class()]))->asParent()->asParent();
    }

    public function __set($key, $value)
    {
        throw (new EntityException('property-not-allowed', ['key'=>$key, 'entity'=>get_called_class()]))->asParent()->asParent();
    }

    public static function primaryKey()
    {
        $a = get_called_class();

        return $a::$primaryKey;
    }

    public function fillFromGet()
    {
        if(isset($_GET)) $this->merge($_GET, true);

        return $this;
    }

    public function fillFromPost()
    {
        if(isset($_POST)) $this->merge($_POST, true);

        return $this;
    }

    public function merge($arr, $ignore=false)
    {
        $arr = (array) $arr;

        if($arr===null) throw (new EntityException('merge-null-data'))->asParent()->asParent();

        foreach($arr as $name => $value)
        {
            try{ $this->{$name} = $value; }

            catch(EntityException $e) { if($ignore) continue; throw $e; }
        }

        return $this;
    }

    public function toArray($getNullFields=false)
    {
        if($getNullFields) return (array) $this;

        $values = [];

        foreach($this as $k=>$v) if($v!==null) $values[$k] = $v;

        return $values;
    }

    public function set($arr, $value="")
    {
        $this->{$arr} = $value;

        return $this;
    }

    public function getKeys()
    {
        return get_object_vars($this);
    }

    public function fill(&$obj=array(), $except=null)
    {
        $keys = get_object_vars($this);

        $a = (is_array($except)) ? true : false ;

        if(is_array($obj))
        {
            foreach($keys as $key=>$v)
            {
                if($a and in_array($key, $except)) continue;

                if($key!=null and $v!==null) $obj[$key] = $v;
            }

            return $obj;
        }

        if(is_object($obj))
        {
            foreach($keys as $key=>$v)
            {
                if($a and in_array($key, $except)) continue;

                if($v!==null) $obj->{$key} = $v;
            }

            return $obj;
        }

    }

    public function setID($id) { $this->{$this->primaryKey()} = $id; return $this; }

    public function getID() { return $this->{$this->primaryKey()}; }

    public function hasID() { return $this->{$this->primaryKey()} ? 1 : 0 ; }

    public static $foreignKeys = [];

    public function link(BaseEntity $entity, $both=false/*Linkar ambos*/)
    {
        $entity_name = get_class($entity);

        $self = get_called_class();

        $entities = array_flip($self::$foreignKeys);

        if(!array_key_exists($entity_name, $entities))

            throw (new EntityException('link-not-allowed'))
                ->set(['self'=>$self, 'entity'=>$entity_name])
                ->asParent()->asParent();

        $key = $entities[$entity_name];

        $this->{$key} = $entity->getID();

        //if($both) $entity->link($this);
    }
}
