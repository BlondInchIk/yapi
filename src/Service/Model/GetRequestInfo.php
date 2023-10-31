<?php

namespace App\Service\Model;

class GetRequestInfo {
    public function __construct(
        public string $type,
        public string $accessToken,
        public string $operation,
        public string $filename,
        public array $content,
    ){}
}