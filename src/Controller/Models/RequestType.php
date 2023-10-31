<?php

namespace App\Controller\Models;

enum  RequestType
{
    case GetFile;
    case Edit;
    function getString(): string{
        return match ($this){
            RequestType::Edit => 'edit',
            RequestType::GetFile => 'getFile',
        };
    }
}