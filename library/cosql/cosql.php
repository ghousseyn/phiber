<?php
namespace cosql;

use \Codup\main;

class cosql extends \PDO {
    
    protected $dbh = null;
    
    function __construct($options = null){
        
        $conf = \Codup\config::getInstance();
        
     try {
        	$this->dbh = parent::__construct('mysql:host='.$conf->_dbhost.';dbname='.$conf->_dbname, $conf->_dbuser, $conf->_dbpass);
        	

        } catch (\PDOException $e) {
        	print "Erreur !: " . $e->getMessage() . "<br/>";
        	die();
        }
        return $this->dbh;
    }
    public static function getInstance(){
        return new self();
    }
    function getCollection(){
     
           //	$this->dbh = new \PDO('mysql:host=localhost;dbname=fq', 'root', 'hggiHmfv');
        	

        $q = $this->dbh->query('SELECT * from dvd');
        $q->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, 'cosql\basemodel'); 
        $collection = new collection;
        while($res = $q->fetch()){
           $collection->add($res);
        }
        return $collection;
    }
}
