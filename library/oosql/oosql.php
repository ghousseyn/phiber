<?php
namespace Phiber\oosql;

use Phiber\entity\entity;

class oosql extends \PDO
{
    /**
     * $oosql_result After a select this field will hold a copy of the result
     * @var collection
     * @access protected
     * @static
     */
    protected static $oosql_result = null;
    /**
     * $oosql_class The class name of the entity
     * @var string
     * @access protected
     */
    protected $oosql_class;
    /**
     * $oosql_table The table we are querying
     * @var string
     * @access protected
     */
    protected $oosql_table;

    /**
     * $oosql_entity_obj An instance of the entity class
     * @var mixed
     * @access protected
     */
    protected $oosql_entity_obj = null;
    /**
     * $oosql_limit
     * @var string Limit clause
     * @access protected
     */
    protected $oosql_limit = null;
    /**
     * $oosql_order
     * @var string Order clause
     * @access protected
     */
    protected $oosql_order = null;
    /**
     * $oosql_where
     * @var string Where clause
     * @access private
     */
    protected $oosql_where = null;
    /**
     * $oosql_join
     * @var string Join clause
     * @access protected
     */
    protected $oosql_join = null;
    /**
     * $oosql_stmt
     * @var Object PDO Statement object
     * @access protected
     */
    protected $oosql_stmt;
    /**
     * $oosql_conValues
     * @var array Parameterized values
     * @access protected
     */
    protected $oosql_conValues = array();
    /**
     * $oosql_numargs
     * @var integer Number of arguments
     * @access protected
     */
    protected $oosql_numargs;
    /**
     * $oosql_fromFlag
     * @var boolean A flag indicating whether From() was executed or not
     * @access protected
     */
    protected $oosql_fromFlag = false;
    /**
     * $oosql_multiFlag
     * @var boolean A flag indicating whether it a mutiple tables query or not
     * @access protected
     */
    protected $oosql_multiFlag = false;
    /**
     * $oosql_del_multiFlag
     * @var boolean A flag indicating whether it is a multiple tables delete or not
     * @access protected
     */
    protected $oosql_del_multiFlag = false;
    /**
     * $oosql_multi
     * @var array Holds the list of tables on multiple table updates
     * @access protected
     */
    protected $oosql_multi = array();
    /**
     * $oosql_del_numargs
     * @var integer Number of arguments (tables) on multi table delete queries
     * @access protected
     */
    protected $oosql_del_numargs;
    /**
     * $oosql_sql
     * @var string The SQL query (never accessed directly)
     * @access protected
     */
    protected $oosql_sql;
    /**
     * $oosql_select
     * @var Object Holds a PDO Statement on SELECTs
     * @access protected
     */
    protected $oosql_select;
    /**
     * $oosql_distinct
     * @var boolean Is the DISTINCT keyword set
     * @access protected
     */
    protected $oosql_distinct = false;
    /**
     * $oosql_insert
     * @var boolean Is it an INSERT
     * @access protected
     */
    protected $oosql_insert = false;
    /**
     * $oosql_sub
     * @var boolean Is this query a sub-query
     * @access protected
     */
    protected $oosql_sub = false;
    /**
     * $oosql_table_alias
     * @var string Table Alias
     * @access protected
     */
    protected $oosql_table_alias;
    /**
     * $oosql_fields
     * @var array Current fields
     * @access protected
     */
    protected $oosql_fields;

    /**
     * $oosql_driver
     * @var string Driver name (set automatically)
     * @access protected
     */
    protected $oosql_driver;
    /**
     * $oosql_in
     * @var string IN clause
     * @access protected
     */
    protected $oosql_in;
    /**
     * $oosql_between
     * @var string BETWEEN clause
     * @access protected
     */
    protected $oosql_between;
    /**
     * $instance
     * @var Object An instance of oosql\oosql
     * @access protected
     */
    protected static $instance;
    /**
     * $oosql_fetchChanged
     * @var bool Flag to check changes in fetch mmode
     * @access protected
     */
    protected $oosql_fetchChanged = false;
    /**
     * group
     * @var string group by clause
     * @access protected
     */
    protected $oosql_group;
    /**
     * Parameters to be passed to parent::prepare()
     * @var array
     * @access protected
     */
    protected $oosql_prepParams = array();
    /**
     * constructor
     * @param string $oosql_table The table we are querying
     * @param string $oosql_class The class name (type of the object holding the results)
     * @param \Phiber\config $config
     * @throws \Exception
     */
    public function __construct($oosql_table = null, $oosql_class = null, $config = null, $options = null)
    {

        if ($oosql_class === null || $oosql_table === null) {
            throw new \Exception('Class or Table name not provided!', 9805, null);
        }
        if (null === $config) {
            $config = \Phiber\config::getInstance();
        }
        $this->oosql_class = $oosql_class;
        $this->oosql_table = $oosql_table;

        parent::__construct($config->PHIBER_DB_DSN, $config->PHIBER_DB_USER, $config->PHIBER_DB_PASS, $options);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
        $this->oosql_driver = $this->getAttribute(\PDO::ATTR_DRIVER_NAME) ;
    }

    /**
     * Get a copy of the results of the previous select (if any) or null if not
     * @return collection or null
     */
    public static function getPrevious()
    {
        return self::$oosql_result;
    }

    /**
     * Get an instance of this class
     * @param string $oosql_table
     * @param string $oosql_class
     * @param \Phiber\config $config
     * @return oosql An oosql\oosql object
     * @static
     */
    public static function getInstance($oosql_table = null, $oosql_class = null, $config = null)
    {
        if (null !== self::$instance) {
            self::$instance->reset();
            self::$instance->setClass($oosql_class);
            self::$instance->setTable($oosql_table);

            return self::$instance;
        }
        return self::$instance = new self($oosql_table, $oosql_class, $config);
    }

    /**
     * Resets the class vars to their initial values for a new query
     * @return \Phiber\oosql\oosql Instance
     */
    public function reset()
    {

        $this->oosql_limit = null;

        $this->oosql_order = null;

        $this->oosql_where = null;

        $this->oosql_join = null;

        $this->oosql_stmt = null;

        $this->oosql_conValues = array();

        $this->oosql_numargs = null;

        $this->oosql_fromFlag = false;

        $this->oosql_multiFlag = false;

        $this->oosql_del_multiFlag = false;

        $this->oosql_multi = array();

        $this->oosql_del_numargs = null;

        $this->oosql_sql = null;

        $this->oosql_select = null;

        $this->oosql_distinct = false;

        $this->oosql_insert = false;

        $this->oosql_sub = false;

        $this->oosql_table_alias = null;

        $this->oosql_fields = array();

        $this->oosql_entity_obj = null;

        $this->oosql_in = null;

        $this->oosql_between = null;

        $this->oosql_fetchChanged = null;

        $this->oosql_group = null;

        $this->oosql_prepParams = null;

        return $this;
    }

    /**
     * Sets the class name for the current table
     * @param string $class Class name of the object to be hydrated
     * @return \Phiber\oosql\oosql Instance
     */
    public function setClass($class)
    {
        $this->oosql_class = $class;
        return $this;
    }

    /**
     * Sets the table name
     * @param string $table Table name
     * @return \Phiber\oosql\oosql Instance
     */
    public function setTable($table)
    {
        $this->oosql_table = $table;
        return $this;
    }

    /**
     * Returns an instance of the entity class used
     * @return entity Instance
     */
    public function getEntityObject()
    {
        if (null != $this->oosql_entity_obj) {
            return $this->oosql_entity_obj;
        } else {
            return new $this->oosql_class($this);

        }
    }

    /**
     * Append to the query string or return the current one
     * @param mixed $sql       SQL
     * @param boolean $replace Replace current query or not
     */
    protected function sql($sql = null, $replace = false)
    {
        if (null !== $sql) {
            if (!isset($this->oosql_sql[$this->oosql_table]) || $replace) {
                $this->oosql_sql[$this->oosql_table] = $sql;
            } else {

                $this->oosql_sql[$this->oosql_table] .= $sql;
            }
            return;
        }
        return $this->oosql_sql[$this->oosql_table];
    }

    /**
     * Create a select statement
     * @variadic
     * @return \Phiber\oosql\oosql Instance
     */
    public function select()
    {
        self::$instance->reset();
        $this->sql('SELECT ');
        if ($this->oosql_distinct) {
            $this->sql('DISTINCT ');
        }
        $numargs = func_num_args();

        if ($numargs > 0) {

            $arg_list = func_get_args();

            $this->oosql_fields = $arg_list;

            for ($i = 0; $i < $numargs; $i++) {
                if ($i != 0 && $numargs > 1) {
                    $this->sql(',');
                }
                $this->sql($arg_list[$i]);

            }
        } else {
            $this->oosql_fields[] = '*';
            $this->sql($this->oosql_table . '.* ');
        }

        $this->oosql_fromFlag = true;
        $this->oosql_select = $this;
        $this->oosql_where = null;
        return $this;
    }

    /**
     * Get an array of fields of the current or given table
     * @param mixed $table table name
     * @return array Table fields
     */
    public function getFields($table = null)
    {
        if (null == $table) {
            $table = $this->oosql_table;
        }
        $newFields = array();
        foreach ($this->oosql_fields as $field) {
            $newFields[] = $table . '.' . $field;
        }
        return $newFields;
    }

    /**
     * Return raw fields array
     * @return array Table fields
     */
    public function getPlainFields()
    {
        return $this->oosql_fields;
    }

    /**
     * Creates an INSERT query
     * @return \Phiber\oosql\oosql Instance
     */
    public function insert()
    {
        self::$instance->reset();
        $this->sql('INSERT INTO ' . $this->oosql_table);

        $arg_list = func_get_args();
        $numargs = func_num_args();

        if ($numargs > 0) {
            $this->oosql_numargs = $numargs;
            $this->sql(' (');

            $this->sql(implode(',', $arg_list));

            $this->sql(')');
        }
        $this->oosql_insert = true;
        return $this;
    }

    /**
     * Creates an UPDATE query
     * @return \Phiber\oosql\oosql Instance
     */
    public function update()
    {
        self::$instance->reset();
        $this->sql('UPDATE');

        $numargs = func_num_args();

        if ($numargs > 0) {
            $arg_list = func_get_args();

            $this->oosql_multiFlag = true;

            $this->oosql_multi = $arg_list;

            for ($i = 0; $i < $numargs; $i++) {
                if ($i != 0 && $numargs > $i) {
                    $this->sql(',');
                }
                $this->sql(' ' . $arg_list[$i]);
            }
        } else {
            $this->sql(" $this->oosql_table");
        }

        $this->sql(' SET ');
        $this->oosql_where = null;
        return $this;
    }

    /**
     * Creates a DELETE query
     * @return \Phiber\oosql\oosql Instance
     */
    public function delete()
    {
        self::$instance->reset();
        $this->sql('DELETE');
        $this->oosql_where = null;
        $numargs = func_num_args();

        if ($numargs > 0) {
            if ($numargs > 1) {
                $this->oosql_del_multiFlag = true;
                $this->oosql_del_numargs = $numargs;
            }
            $arg_list = func_get_args();
            if (is_array($arg_list[0])) {
                $this->sql(' FROM ' . $this->oosql_table);
                $this->where($arg_list[0][0] . ' = ?', $arg_list[0][1]);
                return $this;
            }
            $this->oosql_sql .= ' FROM';
            for ($i = 0; $i < $numargs; $i++) {
                if ($i != 0 && $numargs > 1) {
                    $this->sql(',');
                }
                $this->sql(' ' . $arg_list[$i]);
            }

        } else {
            $this->oosql_fromFlag = true;
        }


        return $this;
    }

    /**
     * Delete a record or more from a table
     * @param mixed $oosql    Optional oosql\oosql instance to run query on
     * @param array $criteria Criteria of current opperation
     * @return \Phiber\oosql\oosql Instance
     */
    public function deleteRecord($oosql = null, array $criteria)
    {
        if (null == $oosql) {
            $oosql = $this;
        }
        $oosql->delete()->createWhere($criteria)->exe();
        return $this;
    }

    /**
     * Sets the column, value pairs in update queries
     * @param array $data An array of the fields with their corresponding values in a key => value format
     * @return \Phiber\oosql\oosql Instance
     */
    public function set(array $data)
    {
        $sql = '';
        foreach ($data as $field => $value) {

            $sql .= $field . ' = ?,';
            $this->oosql_conValues[] = $value;

        }
        $this->sql(rtrim($sql, ','));

        return $this;
    }

    /**
     * Decides if this is an insert or an update and what fields have changed if appropriate
     * @param mixed $object If null this is an insert if not than it's an update
     * @throws \Exception
     * @return object An entity Instance
     */
    public function save(entity $object = null)
    {

        $data = null;
        $primaryValue = array();
        if (null !== $object) {
            $primaryValue = array_filter(array_values($object->getPrimaryValue()), 'strlen');
        }

        if (null === $object || empty($primaryValue)) {
            $entity = $object;
            if (null === $object) {
                if ( null === $this->oosql_entity_obj) {
                    $msg = 'Nothing to save! ' . $this->sql();
                    throw new \Exception($msg, 9806, null);
                }
                // This is a brand new record let's insert;
                $entity = $this->getEntityObject();
            }

            $fields = (array)$entity;

            if ($identity = $entity->identity()) {
                unset($fields[$identity]);
            }
            $populated = array_filter($fields, 'strlen');

            $fieldnames = array_keys($populated);
            $fvalues = array_values($populated);

            call_user_func_array(array($this, 'insert'), $fieldnames);

            $lastID = call_user_func_array(array($this, 'values'), $fvalues)->exe();
            $entity->{$identity} = $lastID;

            return $entity;
        }

        if (isset(self::$oosql_result)) {
            // Updating after a select

            $primaryField = $object->getPrimary();

            $old = self::$oosql_result->objectWhere($primaryField[0], $object->{$primaryField[0]});

            // Is it really a modification of a selected row?
            if ($old) {

                $identity = $this->getEntityObject()->identity();
                foreach (array_diff_assoc((array)$object, (array)$old) as $key => $value) {
                    if ($key == $identity) {
                        continue;
                    }
                    $data[$key] = $value;
                }


                if (null === $data) {
                    $msg = 'Nothing to save! ' . $this->sql();
                    throw new \Exception($msg, 9807, null);
                }
                $this->update()->set($data)->createWhere($object->getPrimaryValue())->exe();

                return $object;

            }

        }
        // update a row just after inserting it
        // Or
        // update a related table (no select on it)
        $identity = $object->identity();

        foreach ((array)$object as $k => $v) {
            if ($v === null || $k == $identity) {
                continue;
            }
            $data[$k] = $v;
        }

        if (count($data) !== 0) {

            $this->update()->set($data)->createWhere($object->getPrimaryValue())->exe();

            return $object;
        }
        $msg = 'Nothing to save! ' . $this->sql();
        throw new \Exception($msg, 9808, null);
    }

    /**
     * Creates where clause(s) from an array of conditions
     * @param array $conditions An array of conditions in the format:
     *                          <code>array("column" => $value)</code>
     * @param string $operator
     * @param string $condition
     * @return \Phiber\oosql\oosql Instance
     */
    public function createWhere(array $conditions, $operator = '=', $condition = 'and')
    {

        foreach ($conditions as $col => $value) {

            if (null === $this->oosql_where) {
                $this->where($col . ' ' . $operator . '?', $value);
                continue;
            }

            $method = $condition . 'Where';

            $this->{$method}($col . ' ' . $operator . '?', $value);

        }

        return $this;
    }

    /**
     * Assembles values part of an insert
     * @throws \Exception
     * @return \Phiber\oosql\oosql Instance
     */
    public function values()
    {

        $arg_list = func_get_args();

        $numargs = func_num_args();

        if (($this->oosql_numargs !== 0 && $numargs !== $this->oosql_numargs) || $numargs === 0) {
            $msg = 'Insert numargs: ' . $this->oosql_numargs . ' | values numargs = ' . $numargs . ', Columns and passed data do not match! ' . $this->sql();
            throw new \Exception($msg, 9809, null);
        }

        $this->sql(' VALUES (');

        for ($i = 0; $i < $numargs; $i++) {
            if ($i != 0 && $numargs > 1) {
                $this->sql(',');
            }

            if (is_array($arg_list[$i])) {
                $this->sql(rtrim(str_repeat('?,', count($arg_list[$i])), ','));
                $this->oosql_conValues += $arg_list[$i];
            } else {
                $this->oosql_conValues[] = $arg_list[$i];
                $this->sql(' ?');
            }
        }


        $this->sql(')');

        $this->oosql_fromFlag = false;
        return $this;
    }

    /**
     * Assembles the FROM part of the query
     * @throws \Exception
     * @return \Phiber\oosql\oosql Instance
     */
    public function from()
    {
        $from = '';

        $numargs = func_num_args();

        if ($this->oosql_del_multiFlag) {


            if ($numargs < $this->oosql_del_numargs) {
                $msg = 'Columns and passed data do not match! ' . $this->sql();
                throw new \Exception($msg, 9810, null);
            }


        }

        $from .= ' FROM ';

        $from .= $this->oosql_table;

        if ($numargs > 0) {
            $from .= ', ';

            $arg_list = func_get_args();

            for ($i = 0; $i < $numargs; $i++) {
                if ($i !== 0 && $numargs > $i) {
                    $from .= ', ';
                }

                if ($arg_list[$i] instanceof oosql) {

                    $arg_list[$i]->alias();
                    $fields = $arg_list[$i]->getFields($arg_list[$i]->getTableAlias());

                    $this->sql(',' . implode(', ', $fields));
                    $from .= $arg_list[$i]->getSql() . ' AS ' . $arg_list[$i]->getTableAlias();
                } else {
                    $from .= $arg_list[$i];
                }
            }
        }

        $this->sql($from);

        $this->oosql_fromFlag = false;

        return $this;
    }

    /**
     * Creates a JOIN clause
     * @param string $table
     * @param string $criteria
     * @param string $type LEFT|RIGHT|FULL OUTER
     * @return \Phiber\oosql\oosql
     */

    public function join($table, $criteria, $type = '')
    {

        $this->oosql_join .= " $type JOIN $table ON $criteria";
        return $this;
    }

    /**
     * Syntactic suagr for LEFT JOINs
     * @param string $table
     * @param string $criteria
     * @return \Phiber\oosql\oosql
     */
    public function joinLeft($table, $criteria)
    {
        return $this->join($table, $criteria, 'LEFT');
    }

    /**
     * Syntactic suagr for RIGHT JOINs
     * @param string $table
     * @param string $criteria
     * @return \Phiber\oosql\oosql
     */
    public function joinRight($table, $criteria)
    {
        return $this->join($table, $criteria, 'RIGHT');
    }

    /**
     * Syntactic suagr for FULL JOINs
     * @param string $table
     * @param string $criteria
     * @return \Phiber\oosql\oosql
     */
    public function joinFull($table, $criteria)
    {
        return $this->join($table, $criteria, 'FULL OUTER');
    }

    /**
     * Assembles a WHERE clause
     * @param string $condition
     * @param string $value
     * @param string $type
     * @return \Phiber\oosql\oosql
     */

    public function where($condition, $value = null, $type = null)
    {

        switch ($type) {
            case null:
                $clause = 'WHERE';
                break;
            case 'or':
                $clause = 'OR';
                break;
            case 'and':
                $clause = 'AND';
                break;
            default:
                $clause = 'WHERE';
        }

        $this->oosql_where .= " $clause $condition";

        if ($value instanceof oosql) {

            $this->oosql_where .= $value->getSql();
        } elseif (null !== $value) {

            $this->oosql_conValues[] = $value;
        }

        return $this;
    }

    /**
     * Declares current query as a sub-query
     * @return \Phiber\oosql\oosql
     */
    public function sub()
    {
        $this->oosql_sub = true;
        $this->exe();
        $this->sql('(' . $this->getSql() . ')', true);

        return $this;
    }

    /**
     * Syntactic sugar to create AND WHERE clause
     * @param string $condition
     * @param mixed $value
     * @return \Phiber\oosql\oosql
     */
    public function andWhere($condition, $value = null)
    {
        $this->where($condition, $value, 'and');
        return $this;
    }

    /**
     * Syntactic sugar to create OR WHERE clause
     * @param string $condition
     * @param mixed $value
     * @return \Phiber\oosql\oosql
     */
    public function orWhere($condition, $value)
    {
        $this->where($condition, $value, 'or');
        return $this;
    }

    /**
     * Prepare a query statement
     * @param array $values Bound values if any
     * @return mixed A \PDOStatement object or the boolean return of PDOStatement::execute
     */
    public function prep($values = null)
    {
        $hash = $this->queryHash();

        $prepOnly = true;

        if (is_array($values)) {

            $prepOnly = false;

        }

        if ($this->oosql_prepParams) {
            $this->oosql_stmt = $this->prepare(trim($this->sql()), $this->oosql_prepParams);
        } else {
            $this->oosql_stmt = $this->prepare(trim($this->sql()));
        }



        if ($prepOnly) {

            return $this->oosql_stmt;

        }

        return $this->execBound($this->oosql_stmt, $values);
    }

    /**
     * Executes a prepared statement with parameterized values
     * @param \PDOStatement $stmt
     * @param array $values
     * @return boolean True on success
     */
    public function execBound(\PDOStatement $stmt, array $values)
    {
        $ord = 1;
        foreach ($values as $val) {

            if (is_bool($val)) {

                $stmt->bindValue($ord, $val, \PDO::PARAM_BOOL);

            } elseif (is_resource($val)) {

                $stmt->bindValue($ord, $val, \PDO::PARAM_LOB);

            } elseif ((string)$val === ((string)(int)$val)) {

                $stmt->bindValue($ord, $val, \PDO::PARAM_INT);

            } else {

                $stmt->bindValue($ord, $val, \PDO::PARAM_STR);
            }
            $ord++;
        }

        return $stmt->execute();
    }

    /**
     * Checks flags and clauses then assembles and executes the query
     * @throws \Exception
     * @return string|object
     * @throws \Exception
     */
    public function exe()
    {

        if ($this->oosql_fromFlag) {
            $this->from();
        }
        if (null != $this->oosql_join) {
            $this->sql($this->oosql_join);
        }
        if (null != $this->oosql_where) {
            $this->sql($this->oosql_where);
        }
        if (null != $this->oosql_in) {
            $this->sql($this->oosql_in);
        }
        if (null != $this->oosql_between) {
            $this->sql($this->oosql_between);
        }
        if (null != $this->oosql_limit) {
            $this->sql(' ' . $this->oosql_limit);
        }
        if (null != $this->oosql_group) {
            $this->sql(' ' . $this->oosql_group);
        }
        if (null != $this->oosql_order) {
            $this->sql(' ' . $this->oosql_order);
        }


        if (count($this->oosql_conValues) !== 0) {

            $return = $this->prep($this->oosql_conValues);
            $this->oosql_conValues = array();
            if ($return === false) {
                $msg = 'Execution failed! ' . $this->sql();
                throw new \Exception($msg, 9811, null);
            }

        } else {

            $this->oosql_stmt = $this->query($this->sql());

        }

        if ($this->oosql_insert) {

            $identity = $this->getEntityObject()->identity();

            if ($identity !== false) {
                if ($this->oosql_driver == 'pgsql') {
                    $identity = $this->oosql_table . '_' . $identity . '_seq';
                }
                $lastID = $this->lastInsertId($identity);

                return $lastID;
            }

        }

        return $this->oosql_stmt;
    }

    /**
     * Returns the results of a SELECT if any
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return \Phiber\oosql\oosql|\Phiber\oosql\collection
     */
    protected function prepFetch()
    {
        if ($this->oosql_sub) {
            return $this;
        }
        if (!$this->oosql_select instanceof oosql) {
            $this->select();
        }
        $this->oosql_select->exe();

        if (!$this->oosql_stmt) {

            $msg = 'Query returned no results! ' . $this->sql();
            throw new \Exception($msg, 9814, null);
        }
        if (!is_array($this->oosql_fetchChanged)) {
            $this->oosql_stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->oosql_class);
        } else {
            call_user_func_array(array($this->oosql_stmt, 'setFetchMode'), $this->oosql_fetchChanged);
        }

    }

    /**
     * Allows changing fetch mode
     *
     * @author Housseyn Guettaf <ghoucine@gmail.com>
     *
     * @link   http://phiber.myjetbrains.com/youtrack/issue/core-35
     *
     * @return $this
     */
    public function setFetchMode()
    {
        $this->oosql_fetchChanged = func_get_args();
        return $this;
    }

    /**
     * Acts as a fetchAll() but returns a collection
     *
     * @author Housseyn Guettaf <ghoucine@gmail.com>
     *
     * @link   http://phiber.myjetbrains.com/youtrack/issue/core-35
     *
     * @return collection
     * @throws \Exception
     */
    public function all()
    {
        $this->prepFetch();

        $result = $this->oosql_stmt->fetchAll();

        $collection = new collection();

        $collection->addBulk($result);

        $collection->obj_name = $this->oosql_class;

        self::$oosql_result = clone $collection;

        return $collection;
    }

    /**
     * Enable the cursor and returns a statement object to allow using fetch() on it
     *
     * @author Housseyn Guettaf <ghoucine@gmail.com>
     *
     * @link   http://phiber.myjetbrains.com/youtrack/issue/core-35
     *
     * @return \PDOStatement
     * @throws \Exception
     */
    public function cursor()
    {
        static $stmt;
        if (!$stmt) {
            $this->setPrepareParams(array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
            $this->prepFetch();
            $stmt = $this->oosql_stmt;
        }

        return $stmt;
    }

    /**
     * Passes parameters to PDO::PREPARE()
     *
     * @author Housseyn Guettaf <ghoucine@gmail.com>
     *
     * @link   http://phiber.myjetbrains.com/youtrack/issue/core-35
     *
     * @param array $params
     * @return $this
     */
    public function setPrepareParams(array $params)
    {
        $this->oosql_prepParams = $params;
        return $this;
    }
    /**
     * Creates a join automatically based on the relationships of current entity
     * @param array $related
     * @return \Phiber\oosql\oosql
     */
    public function with(array $related)
    {

        $relations = $this->getEntityObject()->getRelations();
        foreach ($relations as $fk => $target) {
            $table = strstr($target, '.', true);

            if (in_array($table, $related)) {

                $this->sql(" ,$table.*");
                $this->join($table, "$this->oosql_table.$fk = $target");
            } elseif (isset($related[$table])) {

                foreach ($related[$table] as $field) {
                    $this->sql(" ,$table.$field");
                }
                $this->join($table, "$this->oosql_table.$fk = $target");
            }
        }
        return $this;
    }

    /**
     * Creates a limit clause for MySQL
     * @param integer $from Offset tostart from
     * @param integer $size Chunk size
     * @return \Phiber\oosql\oosql
     */
    public function limit($from, $size)
    {
        if (!$this->oosql_multiFlag) {
            $this->oosql_limit = ' LIMIT ' . $from . ', ' . $size;
        }
        return $this;
    }

    /**
     * Creates an ORDER BY clause
     * @param string $field Field name
     * @param string        DESC|ASC
     * @return \Phiber\oosql\oosql
     */
    public function orderBy($field, $dir = 'ASC')
    {
        if (!$this->oosql_multiFlag) {
            $this->oosql_order = ' ORDER BY ' . $field . ' ' . $dir;
        }
        return $this;
    }

    /**
     * Find first random value returned by the query
     * @param string $arg
     * @param string $operator
     * @param array $fields
     * @return \Phiber\oosql\oosql
     */
    public function findOne($arg = null, $operator = '=', $fields = array('*'))
    {
        return $this->find($arg, $operator, $fields)->limit(0, 1);
    }

    /**
     * Find with limit
     * @param string $arg
     * @param integer $from
     * @param integer $to
     * @param string $operator
     * @param array $fields
     * @return \Phiber\oosql\oosql
     */
    public function findLimited($from, $to, $arg = null, $operator = '=', $fields = array('*'))
    {
        return $this->find($arg, $operator, $fields)->limit($from, $to);
    }

    /**
     * Find records with a filtering condition
     * @param string $arg
     * @param string $operator
     * @param array $fields
     * @return \Phiber\oosql\oosql
     */
    public function find($arg = null, $operator = '=', $fields = array('*'))
    {
        if ($fields[0] == '*') {
            $this->select();
        } else {
            $select_args = '';
            foreach ($fields as $key => $field) {
                if (is_array($field) && is_string($key)) {
                    foreach ($field as $part) {
                        $select_args .= $key . '.' . $part . ', ';
                    }
                } else {
                    $select_args .= $this->oosql_table . '.' . $field . ', ';
                }
            }

            $this->select(rtrim($select_args, ','));
        }
        if (!is_array($arg)) {
            $obj = $this->getEntityObject();
            $pri = $obj->getPrimary();
            if (null !== $arg) {
                $arg = array($pri[0] => $arg);
            }
        }
        $i = 0;
        $flag = '';
        if (is_array($arg)) {
            foreach ($arg as $col => $val) {
                if ($i > 0) {
                    $flag = 'and';
                }
                $this->where("$this->oosql_table.$col $operator ?", $val, $flag);
                $i++;
            }
        }



        return $this;
    }

    /**
     * Creates or bind provided alias with the current loaded table class
     * @param string $alias
     * @return \Phiber\oosql\oosql
     */
    public function alias($alias = null)
    {
        if (null === $alias) {
            $alias = $this->getTableAlias();
        }
        $this->oosql_table_alias = $alias;
        return $this;
    }

    /**
     * Returns the alias for the current table (creates one if none was found)
     * @return string
     */
    public function getTableAlias()
    {
        if (null !== $this->oosql_table_alias) {
            return $this->oosql_table_alias;
        }
        $hash = $this->queryHash();
        $this->oosql_table_alias = $hash;
        return $hash;
    }

    /**
     *Creates a one way hash for the current SQL query
     * @return string
     */
    public function queryHash()
    {
        return hash('sha1', $this->sql());
    }

    /**
     * Creates a GROUP BY clause
     * @param string $field
     * @return \Phiber\oosql\oosql
     */
    public function groupBy($field)
    {
        $this->oosql_group = ' GROUP BY ' . $field;

        return $this;
    }

    /**
     * @todo implement having
     */
    public function having()
    {
    }

    /**
     * Creates a NOT IN clause
     * @param mixed $item  Subject of comparison
     * @param array $list  A list of values
     * @param string $cond OR|AND
     * @param boolean $not
     */
    public function notIn($item, array $list, $cond = null, $not = true)
    {
        return $this->in($item, $list, $cond, $not);
    }

    /**
     * Creates a OR IN clause
     * @param mixed $item  Subject of comparison
     * @param array $list  A list of values
     * @param string $cond OR|AND
     * @param boolean $not
     */
    public function orIn($item, array $list, $cond = 'or', $not = false)
    {
        return $this->in($item, $list, $cond, $not);
    }

    /**
     * Creates an OR NOT IN clause
     * @param mixed $item  Subject of comparison
     * @param array $list  A list of values
     * @param string $cond OR|AND
     * @param boolean $not
     */
    public function orNotIn($item, array $list, $cond = 'or', $not = true)
    {
        return $this->in($item, $list, $cond, $not);
    }

    /**
     * Creates an IN clause
     * @param mixed $item  Subject of comparison
     * @param array $list  A list of values
     * @param string $cond OR|AND
     * @param boolean $not
     */
    public function in($item, array $list, $cond = null, $not = false)
    {
        $inClause = '';

        if (null == $this->oosql_where && null == $this->oosql_in && null == $this->oosql_between) {
            $inClause .= ' WHERE ';
        } else {
            $cnd = ' AND ';

            if (null != $cond) {

                if (strtolower($cond) == 'or') {
                    $cnd = ' OR ';
                }

            }
            $inClause .= $cnd;
        }
        if ($not) {
            $item = $item . ' NOT ';
        }
        $inClause .= $item . ' IN ';

        $obj = $this;

        $list = array_map(function ($data) use ($obj) {
            return (!$obj->validInt($data)) ? $obj->quote($data) : $data;
        }, $list);

        $inClause .= '(' . implode(',', $list) . ')';

        $this->oosql_in = $inClause;

        return $this;
    }

    /**
     * Creates a BETWEEN clause
     * @param mixed $item
     * @param mixed $low
     * @param mixed $up
     * @param string $cond OR|AND
     * @param boolean $not
     * @return \Phiber\oosql\oosql
     */
    public function between($item, $low, $up, $cond = null, $not = false)
    {
        $bClause = '';

        if (null == $this->oosql_where && null == $this->oosql_between && null == $this->oosql_in) {
            $bClause .= ' WHERE ';
        } else {
            $cnd = ' AND ';

            if (null != $cond) {

                if (strtolower($cond) == 'or') {
                    $cnd = ' OR ';
                }

            }
            $bClause .= $cnd;
        }

        if ($not) {
            $item = $item . ' NOT ';
        }
        $bClause .= $item . ' BETWEEN ' . $low . ' AND ' . $up;

        $this->oosql_between = $bClause;

        return $this;
    }

    /**
     * @todo implement union
     */
    public function union()
    {
    }

    /**
     * Make a SELECT DISTINCT
     * @return \Phiber\oosql\oosql
     */
    public function distinct()
    {
        $this->oosql_distinct = true;
        return $this;
    }

    /**
     * Alias for \Phiber\oosql\oosql::transactional()
     * @param callable $fn A Closure
     */
    public function transaction($fn)
    {
        return self::transactional($fn);
    }

    /**
     * Starts a transaction if current driver supports it
     * @param callable $fn A Closure
     * @throws \Exception
     * @return mixed Whatever the Closure returns is passed to the higher scope variable if any
     */
    public static function transactional($fn)
    {
        $oosql = self::getInstance('oosql', 'void');
        if (!$oosql->beginTransaction()) {
            $msg = 'Could not start this transaction. BeginTransaction failed!';
            throw new \Exception($msg, 9815, null);
        }
        if (is_callable($fn)) {
            $ret = $fn($oosql);
            return $ret;
        }
        $msg = 'Please pass a Lamda function as a parameter to this method!';
        throw new \Exception($msg, 9816, null);
    }

    /**
     * Try to obtain a named lock
     *
     * @author Housseyn Guettaf
     *
     * @param string $name    - Lock name
     * @param int    $timeout - Timeout
     *
     * @return mixed
     */
    public function getNamedLock($name, $timeout = 15)
    {
        $stmt = $this->query('SELECT GET_LOCK("' . $name . '", '.$timeout .')');
        return $stmt->fetch(self::FETCH_COLUMN);

    }
    /**
     * releases a named lock
     *
     * @author Housseyn Guettaf
     *
     * @param string $name    - Lock name
     *
     * @return mixed
     */
    public function releaseNamedLock($name)
    {
        $stmt = $this->query('SELECT RELEASE_LOCK("' . $name . '")');
        return $stmt->fetch(self::FETCH_COLUMN);

    }
    /**
     * Returns the currently assembled partial SQL query (complete query is available only after oosql::exe())
     * @return string SQL assemebled until now
     */
    public function getSql()
    {
        return $this->sql();
    }

    /**
     * Magic setter that sets new values on an entity object
     * @param string $var
     * @param string $val
     */
    public function __set($var, $val)
    {

        if (null != $this->oosql_entity_obj) {
            $this->oosql_entity_obj->{$var} = $val;
        } else {
            $this->oosql_entity_obj = $this->getEntityObject();
            $this->oosql_entity_obj->{$var} = $val;
        }

    }
    public function __call($func, $args)
    {
        return call_user_func_array(array($this->getEntityObject(), $func), $args);
    }
}

?>
