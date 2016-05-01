<?php
namespace Language\Api;

use Language\ApiCall as ApiCallStatic;

class ApiCall
{

    public function call($target, $mode, $getParameters, $postParameters)
    {
        return ApiCallStatic::call($target, $mode, $getParameters, $postParameters);
    }
}
