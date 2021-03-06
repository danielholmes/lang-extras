<?php

namespace DHolmes\LangExtras;

abstract class Enumeration
{
    /** @var string */
    private $key;
    /** @var string */
    private $name;
    
    /**
     * @param string $key
     * @param string $name 
     */
    protected function __construct($key, $name)
    {
        $this->key = $key;
        $this->name = $name;
    }
    
    /** @return string */
    public function getKey()
    {
        return $this->key;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param Enumeration $other
     * @return boolean
     */
    public function equals(Enumeration $other)
    {
        return (get_class($this) === get_class($other) &&
                $this->getKey() === $other->getKey());
    }
    
    /** @return string */
    public function __toString()
    {
        return $this->getName();
    }
    
    /** @var array */
    private static $instancesByKey = array();
    
    /** @return array */
    public static function getAll()
    {
        $all = array();
        foreach (static::getValueDescriptions() as $description)
        {
            $all[] = self::getCachedOrCreate($description);
        }
        return $all;
    }
    
    /**
     * @param EnumerationValueDescription $description
     * @return Enumeration
     */
    private static function getCachedOrCreate(EnumerationValueDescription $description)
    {
        $key = $description->getKey();

        // TODO: This should be cached by calling class - to allow the situation of inheritance
        // TODO: Possible issue of conflict between '1' and 1
        if (!isset(self::$instancesByKey[$key]))
        {
            self::$instancesByKey[$key] = static::create($description);
        }
        return self::$instancesByKey[$key];
    }
    
    /**
     * @param string $key 
     * @return Enumeration
     */
    public static function get($key)
    {
        $target = self::getOrNull($key);        
        if ($target === null)
        {
            $class = get_called_class();
            $message = sprintf('"%s" is an invalid value for enumeration %s', $key, $class);
            throw new \InvalidArgumentException($message);
        }
        
        return $target;
    }
    
    /**
     * @param string $key 
     * @return Enumeration
     */
    public static function getOrNull($key)
    {
        $target = null;
        foreach (static::getAll() as $test)
        {
            if ($test->getKey() === $key)
            {
                $target = $test;
                break;
            }
        }
        
        return $target;
    }
    
    /**
     * @param string $key 
     * @return boolean
     */
    public static function has($key)
    {
        $has = false;
        foreach (static::getAll() as $test)
        {
            if ($test->getKey() === $key)
            {
                $has = true;
                break;
            }
        }
        
        return $has;
    }
    
    /** @return array */
    public static function getNamesByKey()
    {
        $namesByKey = array();
        foreach (static::getAll() as $value)
        {
            $namesByKey[$value->getKey()] = $value->getName();
        }
        return $namesByKey;
    }
    
    /** @return array */
    protected static function getValueDescriptions()
    {
        $descriptions = array();

        $foundValues = array();

        $class = get_called_class();
        $refClass = new \ReflectionClass($class);
        foreach ($refClass->getConstants() as $variableName => $value)
        {
            $spacedName = join(' ', explode('_', $variableName));
            $description = ucwords(strtolower($spacedName));
            if (!in_array($value, $foundValues))
            {
                $foundValues[] = $value;
                $descriptions[] = new EnumerationValueDescription($value, $description);
            }
            else
            {
                $message = sprintf('Enumeration values must be unique, "%s" has duplicate of "%s" named "%s"',
                    $class, $value, $variableName);
                throw new \LogicException($message);
            }
        }

        return $descriptions;
    }
    
    /**
     * @param EnumerationValueDescription $description
     * @return mixed
     */
    protected static function create(EnumerationValueDescription $description)
    {
        $args = array($description->getKey(), $description->getDescription());
        
        // TODO: Need to find a way to setAccessible on constructor to make public
        /*
         * $class = new ReflectionClass(get_called_class());
         * $constructor = $class->getConstructor();
         * $constructor->setAccessible(true);
         * 
         * $constructor->invokeArgs(null, $args);
         * $class->newInstanceArgs($args);
         */
        
        return new static($args[0], $args[1]);
    }
    
    /** @return string */
    public static function getClassName()
    {
        return get_called_class();
    }
}
