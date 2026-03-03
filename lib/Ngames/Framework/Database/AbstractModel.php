<?php

/*
 * Copyright (c) 2014-2021 NGames
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ngames\Framework\Database;

use Ngames\Framework\Database\Annotations\Id;
use Ngames\Framework\Database\Annotations\Reference;

/**
 * Abstract class for models classes.
 * Provide utility methods like from and to array and get finder instance.
 *
 */
abstract class AbstractModel
{
    /**
     * Stores metadata for model classes (cache).
     * Keys are class names (as returned by get_class()), values are arrays.
     * Arrays have three keys:
     * - reference_properties: associate a reference to the class it references (user => \Model\User)
     * - properties_mapping: associate the underscore variable name to the camelcase one (read_date => readDate)
     * - primary_key_properties: list of properties that are part of the primary key.
     *
     * @var array
     */
    protected static $metadata = [];


    /**
     * Return the finder instance able to query the database and return instances of current class.
     *
     * @return Finder
     */
    public static function getFinder()
    {
        return new \Ngames\Framework\Database\Finder(get_called_class());
    }

    /**
     * Sets values of current class from an array.
     *
     * @param array $array
     *
     * @return \Ngames\Framework\Database\AbstractModel
     */
    public function fromArray(array $array)
    {
        $classMetadata = $this->getClassMetadata(get_class($this));

        // Store properties for reference. Keys are the property referencing another class, values are arrays of properties for this class
        $referencesProperties = [];
        // Store properties for current class
        $properties = [];

        // Dispatch properties by destination class (current or one of its references)
        foreach ($array as $property => $value) {
            $isReference = false;

            // For all properties that are reference to another class
            if (array_key_exists('reference_properties', $classMetadata)) {
                foreach (array_keys($classMetadata['reference_properties']) as $referenceProperty) {
                    // If current property starts with the reference property name, then it's a value for referenced class
                    if (strpos($property, $referenceProperty . '_') === 0) {
                        $localPropertyName = str_replace($referenceProperty . '_', '', $property);
                        $referencesProperties[$referenceProperty][$localPropertyName] = $value;
                        $isReference = true;
                        break;
                    }
                }
            }

            // Property was not found as a reference, then it's a value for current class
            if (!$isReference && array_key_exists($property, $classMetadata['properties_mapping'])) {
                $properties[$property] = $value;
            }
        }

        // Set my properties
        foreach ($properties as $property => $value) {
            $this->setProperty($classMetadata['properties_mapping'][$property], $value);
        }

        // Set references properties
        foreach ($referencesProperties as $referenceProperty => $referenceProperties) {
            $referenceInstance = new $classMetadata['reference_properties'][$referenceProperty]();
            $referenceInstance->fromArray($referenceProperties);
            $this->setProperty($classMetadata['properties_mapping'][$referenceProperty], $referenceInstance);
        }

        return $this;
    }

    /**
     * Sets an attribute using a setter if it exists, and directly otherwise.
     * @param string $name
     * @param mixed $value
     */
    protected function setProperty($name, $value)
    {
        $setterName = 'set' . ucfirst($name);

        if (method_exists($this, $setterName)) {
            $setterReflection = new \ReflectionMethod($this, $setterName);

            if ($setterReflection->getNumberOfParameters() === 1) {
                return $setterReflection->invoke($this, $value);
            }
        }

        $this->{$name} = $value;
    }

    /**
     * Return the metadata for the provided class.
     *
     * @param string $className
     *
     * @return array
     */
    protected function getClassMetadata($className)
    {
        if (!array_key_exists($className, self::$metadata)) {
            // Get the reflection class and class name
            $reflectionClass = new \ReflectionClass($className);

            // For each property
            $properties = $reflectionClass->getProperties();

            // Initialize metadata
            $classMetadata = [];

            foreach ($properties as $property) {
                // We only want non static properties defined by the sub-class
                if (!$property->isStatic() && $property->getDeclaringClass()->getName() == $className) {
                    $propertyName = $property->getName();
                    $propertyNameUnderscore = \Ngames\Framework\Utility\Inflector::underscore($propertyName);
                    $idAttributes = $property->getAttributes(Id::class);
                    $referenceAttributes = $property->getAttributes(Reference::class);

                    // Add to the list
                    $classMetadata['properties_mapping'][$propertyNameUnderscore] = $propertyName;
                    if (!empty($idAttributes)) {
                        $classMetadata['primary_key_properties'][] = $propertyNameUnderscore;
                    }
                    if (!empty($referenceAttributes)) {
                        $referenceInstance = $referenceAttributes[0]->newInstance();
                        $classMetadata['reference_properties'][$propertyNameUnderscore] = $referenceInstance->targetClass;
                    }
                }
            }

            self::$metadata[$className] = $classMetadata;
        }

        return self::$metadata[$className];
    }
}
