<?php

namespace App\Service\Model;

class ResponseInfo
{
    public function __construct(
        public mixed $data,
        public string $type,
        public string $operation,
        public string $status = "Success",
    ){}
}