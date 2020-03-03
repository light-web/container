<?php

class Foo
{
    public static $link = null;

    public static function getInstance()
    {
        return new static();
    }

    public static function newInstance()
    {
        return new self();
    }

    public static function getStaticProp()
    {
        static $bim;

        $link = 123;

        return $link;
    }
}

$foo = Foo::getInstance();
$foo = Foo::newInstance();

Foo::getStaticProp();

$rl = new ReflectionClass(Foo::class);

var_dump($rl->getProperties());

class Bar extends Foo
{
}

$bar = Bar::getInstance();
$bar = Bar::newInstance();

var_dump($bar);
