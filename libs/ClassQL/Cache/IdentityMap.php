<?php

namespace ClassQL\Cache;

class IdentityMap {

    private static $map = array();
    
    public static function get($className, $id) {
        $id = implode(' ', (array) $id);
        if (isset(self::$map[$className]) && isset(self::$map[$className][$id])) {
            return self::$map[$className][$id];
        }
        return false;
    }

    public static function set($className, $id, $entity) {
        $id = implode(' ', (array) $id);
        if (!isset(self::$map[$className])) {
            self::$map[$className] = array();
        }
        self::$map[$className][$id] = $entity;
    }

}