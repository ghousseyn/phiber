<?php
namespace Phiber\oosql;
require 'collection.php';

abstract class oogen extends \PDO
{

    protected $queries = array();

    protected $except = array();
    protected $errors = array();
    protected $time;
    protected $mem;
    protected $primary = array();
    protected $foreign;
    protected $text;
    protected $ai = false;
    protected $unique = array();
    protected $hasOne = array();
    protected $hasMany = array();
    protected $belongsTo = array();
    protected $manyThrough = array();
    protected $database;

    public $path = './entity/';

    abstract protected function analyze($tbls);

    abstract protected function getMeta($tbls);

    abstract protected function createProps($fields, $tname, $cols);

    public function __construct($dsn, $user, $password)
    {
        parent::__construct($dsn, $user, $password);
        $this->time = microtime(true);
        $this->mem = memory_get_usage();
        $dsnParts = explode('dbname=',str_replace(' ', '',$dsn));
        $this->database = strstr($dsnParts[1], ';', true);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function getCollection($query)
    {

        $stmt = $this->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(self::FETCH_ASSOC);

        if (count($result) == 0) {
            $msg = "Query returned no results!";
            $this->errors[] = array('method' => __METHOD__ . ':' . __LINE__, 'message' => $msg, 'errno' => 9906, 'query' => $query, 'pdo' => 'PDO:' . $this->errorCode());
            return false;
        }

        $collection = new \Phiber\oosql\collection();

        $collection->addBulk($result);

        return $collection;
    }

    public function generate()
    {

        print "Attempting tables discovery..." . PHP_EOL;

        $tables = $this->getCollection($this->queries['tables']);
        if (!$tables) {
            foreach ($this->errors as $error) {
                print implode('|', $error) . PHP_EOL;
            }
            return false;
        }
        foreach ($tables as $tble) {
            $tbls[] = (array)$tble;
        }
        $fields = $this->analyze($tbls);

        if ($fields !== false) {

            foreach ($fields as $tname => $cols) {
                $this->createProps($fields, $tname, $cols);
            }

            $this->createClasses($fields);
        }
        return true;
    }

    protected function createBody($table)
    {

        $table = trim($table);
        $primKey = '';
        $primaryCount = 0;

        if(isset($this->primary[$table])){
            $primKey = $this->primary[$table];
            $primaryCount = count($primKey);
        }

        if ($this->ai !== false) {
            $this->text .= PHP_EOL . '  public function identity()' . PHP_EOL . '  {' . PHP_EOL;
            $this->text .= '    return "' . $this->ai . '"';
            $this->text .= ';' . PHP_EOL . '  }' . PHP_EOL;
        }

        if ($primaryCount !== 0) {

            $this->text .= PHP_EOL . '  public function getPrimary()' . PHP_EOL . '  {' . PHP_EOL . '    return array("' . implode('","', $primKey) . '");' . PHP_EOL . '  }' . PHP_EOL;

            if ($primaryCount > 1) {
                $this->text .= '  public function getPrimaryValue($key = null)' . PHP_EOL . '  {' . PHP_EOL . '    if(null === $key){' . PHP_EOL . '      return $this->getCompositeValue();' . PHP_EOL . '    }' . PHP_EOL . '    $pri = $this->getPrimary();' . PHP_EOL . '    if(in_array($key,$pri)){' . PHP_EOL . '      return $this->{$key};' . PHP_EOL . '    }' . PHP_EOL . '  }' . PHP_EOL;
                $this->text .= '  public function getCompositeValue()' . PHP_EOL . '  {' . PHP_EOL . '    return array(' . PHP_EOL;
                foreach ($primKey as $pkey) {
                    $this->text .= '            "' . $pkey . '" => $this->' . $pkey . ',' . PHP_EOL;
                }
                $this->text .= '        );' . PHP_EOL . '  }' . PHP_EOL;
            } else {
                $this->text .= '  public function getPrimaryValue($key=null)' . PHP_EOL . '  {' . PHP_EOL . '    if(null === $key){' . PHP_EOL . '      return array("' . implode("", $primKey) . '" => $this->' . implode("", $primKey) . ');' . PHP_EOL . '    }' . PHP_EOL . '    $pri = $this->getPrimary();' . PHP_EOL . '    if(in_array($key,$pri)){' . PHP_EOL . '      return $this->{$key};' . PHP_EOL . '    }' . PHP_EOL . '  }' . PHP_EOL;

            }
            // unset($primary);
        }


        if (isset($this->foreign[$table]) && count($this->foreign[$table])) {
            $this->text .= '  public function getRelations()' . PHP_EOL . '  {' . PHP_EOL . '    return array(';
            foreach ($this->foreign[$table] as $member => $content) {
                $this->text .= "'" . $member . "'=>'" . $content[1] . "." . trim(str_replace('`)', '', $content[0])) . "',";
            }
            $this->text .= ');' . PHP_EOL . '  }' . PHP_EOL;
        }

        if (isset($this->belongsTo[$table]) && count($this->belongsTo[$table])) {
            $bto = self::transcribe($this->belongsTo[$table]);
            $this->text .= '  public function belongsTo()' . PHP_EOL . '  {' . PHP_EOL . '    return ' . $bto . ';' . PHP_EOL . '  }' . PHP_EOL;
        }

        if (isset($this->hasOne[$table]) && count($this->hasOne[$table])) {

            $hone = self::transcribe($this->hasOne[$table]);
            $this->text .= '  public function hasOne()' . PHP_EOL . '  {' . PHP_EOL . '    return ' . $hone . ';' . PHP_EOL . '  }' . PHP_EOL;
        }
        if (isset($this->hasMany[$table]) && count($this->hasMany[$table])) {

            $hmany = self::transcribe($this->hasMany[$table]);
            $this->text .= '  public function hasMany()' . PHP_EOL . '  {' . PHP_EOL . '    return ' . $hmany . ';' . PHP_EOL . '  }' . PHP_EOL;
        }

        if (isset($this->manyThrough[$table])) {

            $hmanyThrough = self::transcribe($this->manyThrough[$table]);
            $this->text .= '  public function hasManyThrough()' . PHP_EOL . '  {' . PHP_EOL . '    return ' . $hmanyThrough . ';' . PHP_EOL . '  }' . PHP_EOL;
        }
        $this->text .= '}' . PHP_EOL;


    }

    protected function createClasses($fields)
    {

        $h = 0;
        $this->text = '';



        foreach ($fields as $tname => $cols) {

            $metas = $this->getMeta($tname);

            $cname = $tname;

            print "Generating class $cname ...";

            $this->text .= '<?php' . PHP_EOL . 'namespace entity;' . PHP_EOL . 'use Phiber;';
            $this->text .= PHP_EOL . "class $cname extends Phiber\\entity\\entity  " . PHP_EOL . "{" . PHP_EOL;

            $this->ai = false;



            foreach ($metas as $colName => $meta) {
                $this->text .= '  const '.$colName.'_META = \''.json_encode($meta).'\';' . PHP_EOL;
            }
            $this->text .=  PHP_EOL;
            foreach ($metas as $colName => $meta) {
                $this->text .= '  public $' . $colName . ';' . PHP_EOL;
            }

            foreach ($cols as $col) {


                if (isset($col['Extra']) && $col['Extra'] == 'auto_increment') {
                    $this->ai = $col['Field'];
                }

            }


            $this->createBody($cname);

            $filename = rtrim($this->path, '/,\\') . DIRECTORY_SEPARATOR . $cname . ".php";

            $f = fopen($filename, "w+");
            fwrite($f, $this->text);
            fclose($f);
            $this->text = '';
            print " Done" . PHP_EOL;
            $h++;
        }
        print "Generated " . $h . " classes in " . number_format((microtime(true) - $this->time), 4) . " ms | Memory: " . number_format((memory_get_usage() - $this->mem) / 1024, 4) . "kb";
    }

    public static function transcribe(array $array)
    {
        $json = json_encode($array, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        $text = '';
        $text .= str_replace(array('[', ']', '{', '}', ':'), array('array(', ')', 'array(', ')', ' =>'), $json);
        return $text;
    }

}
