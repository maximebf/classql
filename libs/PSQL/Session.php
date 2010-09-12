<?php

namespace PSQL;

final class Session
{
    private static $_connection;
    
    private static $_cache;
    
    private static $_parser;
    
    private static $_generator;
    
    public static function registerAutoloader()
    {
	    spl_autoload_register(function($className) {
	        $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
	        require_once $filename;
	    });
    }
    
    public static function registerStreamWrapper()
    {
        StreamWrapper::register('psql');
    }
    
    public static function configure(Connection $connection, Cache $cache = null, 
        Parser $parser = null, Generator $generator = null)
    {
        self::$_connection = $connection;
        self::$_cache = $cache ?: new NullCache();
        self::$_parser = $parser ?: new Parser();
        self::$_generator = $generator ?: new Generator();
    }
    
    public static function getConnection()
    {
        return self::$_connection;
    }
    
    public static function getCache()
    {
        return self::$_cache;
    }
    
    public static function getParser()
    {
        return self::$_parser;
    }
    
    public static function getGenerator()
    {
        return self::$_generator;
    }
}

