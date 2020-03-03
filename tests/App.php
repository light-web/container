<?php

namespace tests;

class App
{
    public function show()
    {
        return 'hello';
    }

    public function spider(Spider $spider ){

        return $spider->run();
    }
}
