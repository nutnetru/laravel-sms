<?php
/**
 * 27/09/2019
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */

namespace Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @param $class
     * @param $method
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    protected function makeMethodAccessible($class, $method)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }
}