<?php

namespace Phiber\oosql;

class stmtCache
{
    protected static $hashes = [];
    public static function setHash($hash, $stmt)
    {
        self::$hashes[$hash] = $stmt;
    }
    public static function getByHash($hash)
    {
        if (empty(self::$hashes[$hash])) {
            return false;
        }
        return self::$hashes[$hash];
    }
}

?>
