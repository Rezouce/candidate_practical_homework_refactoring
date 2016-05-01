<?php

namespace Language\Config;

use Language\Config as ConfigStatic;

class Config
{

    public function get($key)
    {
        return ConfigStatic::get($key);
    }
}
