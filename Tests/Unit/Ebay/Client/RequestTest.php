<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Eltrino\OroCrmEbayBundle\Tests\Unit\Ebay\Client;

use Eltrino\OroCrmEbayBundle\Ebay\Client\Request;
use Eltrino\OroCrmEbayBundle\Ebay\Filters\Filter;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /** @var Request */
    protected $object;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Filter */
    protected $filter;

    public function setUp()
    {
        $this->filter = $this
            ->getMockBuilder('Eltrino\OroCrmEbayBundle\Ebay\Filters\Filter')
            ->getMock();

        $this->filter
            ->expects($this->any())
            ->method('process')
            ->willReturn(['filters_test']);

        $this->object = new Request('test', $this->filter, ['test']);
    }

    /**
     * @dataProvider testGettersDataProvider
     */
    public function testMethods($method, $expected)
    {
        $this->assertEquals($expected, $this->object->{$method}('body'));
    }

    public function testGettersDataProvider()
    {
        return [
            'getParameters'            => ['getParameters', ['test']],
            'getAction'                => ['getAction', 'test'],
            'processFiltersParameters' => ['processFiltersParameters', ['filters_test']]
        ];
    }
}
