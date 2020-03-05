<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use zeni18\container\build\Core;
use zeni18\container\Container;

/**
 * @internal
 * @coversNothing
 */
class ContainerTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        echo "=========================\n";
    }

    public function testSingle()
    {
        $container = Container::single();

        $this->assertInstanceOf(Core::class, $container);
    }

    public function testBind()
    {
        $this->assertInstanceOf(App::class, Container::bind(App::class, function ($c) {
            return new  App();
        })->make(App::class));
    }

    public function testMake()
    {
        $this->assertEquals('hello', Container::make(App::class)->show());
    }

    public function testDI()
    {
        $msg = (new Spider())->run();

        $this->assertEquals($msg, Container::callMethod(App::class, "spider"));
    }

    public function testInstance()
    {
        Container::instance("phpunit", $this);

        $this->assertEquals($this, Container::make("phpunit"));

    }

}
