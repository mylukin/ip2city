<?php

namespace Mylukin\Ip2city;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @see Overtrue\Wechat\Wechat
 */
class Facade extends LaravelFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ip2city';
    }
}