<?php

namespace evandroaugusto\DBCache;


class DBCacheService
{
    private $fetcher;


    public function __construct(\PDO $db, $table = 'cache')
    {
        $this->fetcher = new DBCache\DBCacheRepository($db, $table);
    }

    /**
     * Get a valid cache (not expired)
     *
     * It's smarter than get(), first check if the cache is expired before returning value
     */
    public function getCache($cacheId)
    {
        if (!isset($cacheId)) {
            throw new \Exception("Error Processing Request");
        }

        // validate cache and check if it needs to be renewed
        if ($objCache = $this->get($cacheId)) {
            if (time() < $objCache->expire) {
                return $objCache;
            }
        }

        // clear cache
        $this->clear($cacheId);

        return false;
    }

    /**
     * Get cache from database
     *
     * @param  string $cacheId Cache identifier
     * @return bool
     */
    public function get($cacheId)
    {
        if (!isset($cacheId)) {
            throw new \Exception('You need to specify an cache_id');
        }

        try {
            // get content from cache_id
            $cache = $this->fetcher->get($cacheId);

            // check result and unserialize result
            if ($cache) {
                if ($cache->serialized == 1) {
                    $cache->data = unserialize($result->data);
                }

                return $cache;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Store value in the cache
     *
     * @param string $cacheId Cache indetifier
     * @param string $value   The value to be stored
     */
    public function set($cacheId, $value, $expire=null)
    {
        $serialized = 0;
        $expire = $expire ?? time() + (60*60*3); // default to 2 hours

        if (!isset($cacheId, $value) || !isset($value)) {
            throw new \Exception('You must set a Key and a Value to be stored');
        }

        // object and arrays are serialized
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
            $serialized = 1;
        }

        try {
            // clear current cache
            $this->clear($cacheId);
            $cache = $this->fetcher->set($cacheId, $value, $expire);
            
            return true;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Clear the cache from DB
     * 
     * @param  string $cacheId Cache identifier
     * @return bool
     */
    public function clear($cacheId)
    {
        if (!isset($cacheId)) {
            throw new \Exception('You must set and cache id to be cleared');
        }

        // clear some specific id, bd stuff...
        try {
            $cache = $this->fetcher->clear($cacheId);
            return true;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Clear cache from wildcard
     *
     * @param string $wildCard
     * @return boolean
     */
    public function clearAll($wildCard)
    {
        if (!isset($wildCard)) {
            throw new \Exception('You must set a wildcard string to identify cache results to be cleared');
        }

        // clear some specific ids, bd stuff...
        try {
            $this->fetcher->clearAll($cacheId);
            return true;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
}
