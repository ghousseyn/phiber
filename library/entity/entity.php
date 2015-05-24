<?php
/**
 * Entity class.
 * @version    1.0
 * @author     Housseyn Guettaf <ghoucine@gmail.com>
 * @package    Phiber
 */
namespace Phiber\entity;

use Phiber\oosql\oosql;

abstract class entity
{

    protected static $oosql_model_extra = false;
    protected static $oosql_obj;
    protected static $tablename;


    public function __construct($oosql = null)
    {
        if (null !== $oosql) {
            static::$oosql_obj = $oosql;
        } else {
            static::$oosql_obj = static::getooSQL(get_class($this));
        }

    }

    public function getTableName()
    {
        $parts = explode('\\', get_called_class());
        return array_pop($parts);
    }

    /*
     * Deliver an oosql instance instead and pass this class name so we can get
     * back with the caller instance with results
     */
    public static function getInstance()
    {
        return self::getooSQL(get_class(new static));
    }

    protected static function getooSQL($class)
    {

        self::$tablename = strstr($class, '\\');
        if (self::$tablename === false) {
            return false;
        }

        self::$tablename = trim(str_replace('\\', '', self::$tablename));
        return self::$oosql_obj = oosql::getInstance(self::$tablename, $class);
    }

    public function save($saveRelated = false)
    {

        $instances = array();
        self::$oosql_obj->setTable($this->getTableName());
        if (self::$oosql_model_extra) {

            $originalProps = get_object_vars(new static);

            $mixedProps = array_keys(get_object_vars($this));


            foreach ($mixedProps as $property) {

                if (!array_key_exists($property, $originalProps)) {

                    if ($saveRelated) {

                        foreach ($this->getRelations() as $fk => $relation) {

                            $table = strstr($relation, '.', true);
                            $field = ltrim(strstr($relation, '.'), '.');
                            if (is_array($saveRelated)) {
                                if (!in_array($table, $saveRelated)) {
                                    continue;
                                }
                            }
                            $entityname = "entity\\$table";
                            $classVars = get_class_vars($entityname);

                            if (array_key_exists($property, $classVars)) {

                                $hash = hash('adler32', $entityname);

                                if (isset($instances[$hash])) {
                                    $instance = $instances[$hash];
                                } else {
                                    $instance = new $entityname;
                                }
                                //Set the property in the related object
                                $instance->{$property} = $this->{$property};

                                $instance->{$field} = $this->{$fk};

                                $instances[$hash] = $instance;

                                unset($instance);
                            }

                        }


                    }
                    unset($this->{$property});
                }
            }

        }

        if ($saveRelated && count($instances)) {

            try {
                foreach ($instances as $inst) {
                    $inst->save();
                }
                return $this->save();
            } catch (\Exception $e) {
                throw $e;
            }

        } else {
            return $this->reset()->save($this);
        }
    }

    public function reset()
    {
        return self::$oosql_obj->reset();
    }

    public function load()
    {

            return $this->find();

    }

    public function __set($var, $val)
    {
        if (!array_key_exists($var, get_object_vars(new static))) {
            self::$oosql_model_extra = true;
        }
        $this->{$var} = $val;
    }

    public function __unset($property)
    {
        unset($this->properties[$property]);
    }

    public function __call($tablename, $args)
    {
        $relations = $this->getRelations();

        foreach ($relations as $fk => $target) {
            $related = $this->execRelation($tablename, $fk, $target, $args);
            if ($related instanceof entity || $related instanceof \Phiber\oosql\collection) {
                return $related;
            }
        }

        $relations = $this->hasOne();

        foreach ($relations as $localKey => $related) {
            foreach ($related as $relStr) {
                $related = $this->execRelation($tablename, $localKey, $relStr, $args);
                if ($related instanceof entity || $related instanceof \Phiber\oosql\collection) {
                    return $related;
                }
            }

        }

        $relations = $this->hasMany();

        foreach ($relations as $localKey => $related) {
            foreach ($related as $relStr) {
                $related = $this->execRelation($tablename, $localKey, $relStr, $args);
                if ($related instanceof entity || $related instanceof \Phiber\oosql\collection) {
                    return $related;
                }
            }

        }

        $relations = $this->hasManyThrough();

        foreach ($relations as $table => $relatedTbls) {
            if (in_array($tablename, $relatedTbls)) {
                $collection = new \Phiber\oosql\collection();
                if (!empty($args)) {
                    $objCollection = $this->{$table}()->load();
                    if (!$objCollection->isEmpty()) {
                        foreach ($objCollection as $record) {
                            $Recs[] = $record->{$tablename}()->load()->toArray();
                        }
                        $flattened = array();
                        array_walk_recursive($Recs, function ($a) use (&$flattened) {
                            $flattened[] = $a;
                        });
                        $collection->addBulk($flattened);
                    }
                } else {
                    $objCollection = $this->{$table}()->load();
                    if (!$objCollection->isEmpty()) {

                        foreach ($objCollection as $res) {
                            $objects[] = $res->{$tablename}();
                        }

                        $flattened = array();
                        array_walk_recursive($objects, function($a) use (&$flattened) { $flattened[] = $a; });
                        $collection->addBulk($flattened);

                    }
                }
                return $collection;
            }
        }



    }

    protected function execRelation($tablename, $key, $relationString, $args)
    {
        $objPath = explode('.', $relationString);
        if ($objPath[0] == $tablename) {
            if (null == $this->{$key}) {
                throw new \Exception('The relation key "' . $key . '" is null!');
            }
            $tablename = "entity\\$tablename";
            $instance = new $tablename;
            $instance->{$objPath[1]} = $this->{$key};
            if ($args) {
                return $instance->load();
            }
            return $instance;
        }
        return false;
    }
    public function __get($var)
    {
        if (array_key_exists($var, get_object_vars(new static))) {
            return $this->{$var}();
        }

    }

    private function callFunc($fn, $args)
    {
        $class = get_class($this);
        return call_user_func_array(array(self::getoosql($class), $fn), $args);
    }

    public function select()
    {
        return $this->callFunc('select', func_get_args());
    }

    public function insert()
    {
        return $this->callFunc('insert', func_get_args());
    }

    public function update()
    {
        return $this->callFunc('update', func_get_args());
    }

    public function find()
    {
        $args = func_get_args();
        $filter = null;
        if(!$args){
            foreach (get_object_vars($this) as $property => $val) {
                if (null !== $val) {
                    $filter[$property] = $val;
                }
            }
            $args[] = $filter;
        }

        return $this->callFunc('find', $args)->fetch();
    }
    public function findOne()
    {
        return $this->callFunc('findOne', func_get_args())->fetch();
    }
    public function findLimited()
    {
        return $this->callFunc('findLimited', func_get_args())->fetch();
    }
    public function delete()
    {
        $class = get_class($this);
        return $this->callFunc('deleteRecord', array(self::getoosql($class), $this->getPrimaryValue()));
    }

    public function getPrimary()
    {
        return array();
    }

    public function getPrimaryValue()
    {
        return array();
    }

    public function getCompositeValue()
    {
        return array();
    }

    public function getRelations()
    {
        return array();
    }
    public function hasManyThrough()
    {
        return array();
    }
    public function belongsTo()
    {
        return array();
    }

    public function hasOne()
    {
        return array();
    }

    public function hasMany()
    {
        return array();
    }
    public function typeOf($field)
    {
        $const = $this->getFieldMeta($field);
        return $const[0];
    }
    public function getFieldTypes($fields = null)
    {
        $types= array();

        $constants = $this->getMeta();
        if($fields && is_array($fields)){
            foreach ($fields as $field){
                $key = $field.'_META';
                if(array_key_exists($key, $constants)){
                    $meta = json_decode($constants[$key]);
                    $types[$field] = $meta[0];
                }
            }
            return $types;
        }
        foreach ($constants as $field => $meta){
            $meta = json_decode($meta);
            $field = preg_replace('/_META$/', '', $field);
            $types[$field] = $meta[0];
        }

        return $types;
    }
    public function lengthOf($field)
    {
        $const = $this->getFieldMeta($field);
        return $const[1];
    }
    public function getFieldMeta($field)
    {
        $const = $field.'_META';
        return json_decode(constant("static::$const"));
    }
    public function getMeta()
    {
        $obj = new \ReflectionClass(get_called_class());
        return $obj->getConstants();
    }
}

?>