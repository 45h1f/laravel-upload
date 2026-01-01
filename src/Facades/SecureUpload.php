<?php

namespace Ashiful\Upload\Facades;

use Illuminate\Support\Facades\Facade;

class SecureUpload extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ashiful.upload';
    }
}
