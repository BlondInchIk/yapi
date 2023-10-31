<?php

namespace App\Controller\Models;

class TokenAnswer {
    public function __construct(
        public string $session_id,
        public string $token
    ){}
}