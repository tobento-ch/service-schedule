<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Schedule\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\Test\Mock;
use Tobento\Service\Schedule\Parameters;
use Tobento\Service\Schedule\ParametersInterface;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\Parameter;

class ParametersTest extends TestCase
{
    public function testThatImplementsParametersInterface()
    {
        $this->assertInstanceof(ParametersInterface::class, new Parameters());
    }
    
    public function testConstructMethod()
    {
        $monitor = new Parameter\Monitor();
        $params = new Parameters($monitor);
        
        $this->assertSame($monitor, $params->first());
    }
    
    public function testAddMethod()
    {
        $monitor = new Parameter\Monitor();
        $params = new Parameters();
        $params->add($monitor);
        
        $this->assertSame($monitor, $params->first());
    }
    
    public function testFilterMethod()
    {
        $params = new Parameters();
        $params->add(new Parameter\Monitor());
        $params->add(new Parameter\SendResultTo('dir/file.log'));
        
        $paramsNew = $params->filter(fn (ParameterInterface $param) => $param instanceof Parameter\SendResultTo);
        
        $this->assertCount(2, $params->all());
        $this->assertFalse($params === $paramsNew);
        $this->assertCount(1, $paramsNew->all());
    }
    
    public function testNameMethod()
    {
        $params = new Parameters();
        $params->add(new Parameter\Monitor());
        $params->add(new Parameter\SendResultTo('dir/file.log'));
        
        $paramsNew = $params->name(Parameter\SendResultTo::class);
        
        $this->assertCount(2, $params->all());
        $this->assertFalse($params === $paramsNew);
        $this->assertCount(1, $paramsNew->all());
    }
    
    public function testSortMethodHighestFirst()
    {
        $params = new Parameters();
        $params->add(new Mock\Param(name: 'foo', priority: 1));
        $params->add(new Mock\Param(name: 'bar', priority: 3));
        $params->add(new Mock\Param(name: 'baz', priority: 2));
        
        $paramsNew = $params->sort();
        
        $this->assertFalse($params === $paramsNew);
        $this->assertSame(['foo', 'bar', 'baz'], array_map(fn ($p) => $p->getName(), $params->all()));
        $this->assertSame(['bar', 'baz', 'foo'], array_values(array_map(fn ($p) => $p->getName(), $paramsNew->all())));
    }
    
    public function testFirstMethod()
    {
        $params = new Parameters();
        
        $this->assertNull($params->first());
        
        $monitor = new Parameter\Monitor();
        $params = new Parameters($monitor);
        
        $this->assertSame($monitor, $params->first());
    }
    
    public function testAllMethod()
    {
        $params = new Parameters();
        
        $this->assertCount(0, $params->all());
        
        $params->add(new Parameter\Monitor());
        $params->add(new Parameter\SendResultTo('dir/file.log'));
        
        $this->assertCount(2, $params->all());
    }

    public function testGetIteratorMethod()
    {
        $params = new Parameters();
        
        $params->add(new Parameter\Monitor());
        $params->add(new Parameter\SendResultTo('dir/file.log'));
        
        $this->assertCount(2, $params->getIterator());
    }
}