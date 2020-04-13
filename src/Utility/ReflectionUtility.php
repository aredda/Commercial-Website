<?php

namespace App\Utility;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ManyToOne;
use Exception;
use ReflectionClass;
use ReflectionProperty;

abstract class ReflectionUtility
{
    public const GETTER = 'get';
    public const SETTER = 'set';

    /**
     * Converts an associative array to the targeted entity name
     */
    public static function toObject (array $data, string $entityName, EntityManager $entityManager, $target = null)
    {
        # Retrieve the reflection class helper
        $reflector = new ReflectionClass ($entityName);
        # Get the annotation reader
        $reader = new AnnotationReader();
        # Create a plain object
        $instance = $target ?? $reflector->newInstance ();
        # Loop through data array
        foreach ($data as $key => $value)
        {
            # Check if the Entity has a property of that name
            if (!$reflector->hasProperty ($key)) continue;
            # Retrieve the property
            $property = $reflector->getProperty ($key);
            # Retrieve the setter of this property
            $setter = self::getSetter($property);
            # Check if there's a setter
            if ($setter == null) continue;
            # Prepare a value to deliver to the instance
            $propValue = $value;
            # Check if it's a reference to another Entity
            if (($annotation = $reader->getPropertyAnnotation($property, ManyToOne::class)) != null)
                $propValue = $entityManager->find ($annotation->targetEntity, $value);
            # Set the value of the property
            $setter->invoke ($instance, $propValue);
        }
        # Return object
        return $instance;
    }

    /**
     * Get the setter/getter of a property, returns null if there's none
     */
    private static function getEncapsulator (string $type, ReflectionProperty $property)
    {
        try
        {
            return $property->getDeclaringClass ()->getMethod ($type . ucfirst($property->getName ()));
        }
        catch (Exception $x) { return null; }
    }

    /**
     * Get the setter for this property
     */
    public static function getSetter (ReflectionProperty $property)
    {
        return self::getEncapsulator (self::SETTER, $property);
    }

    /**
     * Get the getter for this property
     */
    public static function getGetter (ReflectionProperty $property)
    {
        return self::getEncapsulator (self::GETTER, $property);
    }    

    /**
     * Gets an annotation of a property, returns null if id doesn't exist
     */
    public static function getAnnotation (ReflectionProperty $property, string $annotation)
    {
        return (new AnnotationReader())->getPropertyAnnotation ($property, $annotation);
    }

    /**
     * Get property, null if there's no property whose name matches the requested property
     */
    public static function getProperty (string $className, string $propertyName)
    {
        # Initialize a reflection helper
        $reflector = new ReflectionClass ($className);
        # Check if the class has the requested property
        if (!$reflector->hasProperty ($propertyName)) return null;
        # Return the requested property
        return $reflector->getProperty ($propertyName);
    }
}