<?php

namespace tests;

/**
 * Class Spider.
 */
class Spider
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function run($name = 'spider')
    {
        return sprintf('%s: run tasks......', $name);
    }
}
