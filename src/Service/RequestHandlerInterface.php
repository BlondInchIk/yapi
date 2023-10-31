<?php

namespace App\Service;

use App\Controller\Models\RequestData;
use App\Controller\Models\TokenAnswer;
use App\Service\Model\GetRequestInfo;

interface RequestHandlerInterface
{
    function generateGetRequest(TokenAnswer $answer, RequestData $data, string $operation): ?GetRequestInfo;
}