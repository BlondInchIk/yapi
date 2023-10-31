<?php

namespace App\Controller\Models;

class RequestData {
    public function __construct(
        public string $session_id,
        public string $requestType,
        public array $data
    ){}
}
