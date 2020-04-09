<?php

namespace evandroaugusto\DBCache;


class DBCacheRepository
{
    const prefixTable = 'cache_';

    private $db;
    private $table;


    public function __construct(\PDO $db, $table = 'cache')
    {
        $this->db = $db;
        $this->setTable($table);
    }

    /**
     * Get cache from database
     *
     * @param  string $cacheId Cache identifier
     * @return bool
     */
    public function get($cacheId)
    {
        // db statement
        $query = $this->db->prepare('
            SELECT
                cache_id, data, expire, created, serialized
            FROM
                ' . $this->table . '
            WHERE
                cache_id = :cache_id
        ');

        $query->execute(array(
            ':cache_id' => $cacheId,
        ));

        // return in object
        $PDO =& $this->db;
        
        return  $query->fetch($PDO::FETCH_OBJ);
    }


    /**
     * Store value in the cache
     *
     * @param string $cacheId Cache indetifier
     * @param string $value   The value to be stored
     */
    public function set($cacheId, $value, $expire)
    {
        $query = $this->db->prepare('
            INSERT INTO ' . $this->table . ' (
                cache_id, 
                data, 
                created, 
                expire, 
                serialized
            ) VALUES (
                :cache_id, 
                :data, 
                :created, 
                :expire, 
                :serialized
            )
        ');

        $query->execute(array(
            ':cache_id'   => $cacheId,
            ':data'       => $value,
            ':created'    => time(),
            ':expire'     => $expire,
            ':serialized' => $serialized,
        ));

        return $query->rowCount();
    }

    /**
     * Clear the cache from DB
     *
     * @param  string $cacheId Cache identifier
     * @return bool
     */
    public function clear($cacheId)
    {
        // clear a specific cache id
        $query = $this->db->prepare(
            'DELETE FROM ' . $this->table . '
          WHERE cache_id = :cache_id
        '
        );

        $query->execute(array(
            ':cache_id' => $cacheId
        ));

        return $query->rowCount();
    }

    /**
     * Clear all cache from wildard
     *
     * @param string $wildCard
     * @return void
     */
    public function clearAll($wildCard)
    {
        // clear some specific id, bd stuff...
        $query = $this->db->prepare('
            DELETE FROM ' . $this->table . '
            WHERE cache_id LIKE :wildcard
        ');

        $query->execute(array(
            ':wildcard' => '%'.$wildCard.'%'
        ));

        return $query->rowCount();
    }


    /**
     * Set the cache table, default is 'cache'
     *
     * @param string $table Table name to be used
     */
    public function setTable($table)
    {
        if (!isset($table)) {
            throw new \Exception('You cant define an empty value', 1);
            return false;
        }

        // default cache table is 'cache'
        if ($table !== 'cache') {
            $this->table = self::prefixTable . $table;
        } else {
            $this->table = 'cache';
        }
    }
}
