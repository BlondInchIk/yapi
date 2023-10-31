<?php

namespace App\Service;

use App\Controller\Models\RequestData;
use App\Controller\Models\TokenAnswer;
use App\Service\Model\GetRequestInfo;
use PHPUnit\Util\Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Rx\Scheduler;
use Rx\Scheduler\VirtualTimeScheduler;
use Rx\Subject\BehaviorSubject;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class RequestHandlerService implements RequestHandlerInterface {
    private static ?RequestHandlerService $service = null;


    /**
     * @throws \Exception
     */
    static function get(): RequestHandlerService{
        if(self::$service === null){
            self::$service = new self();
            self::$service->random = random_int(0,10000);
        }
        return self::$service;
    }

    function generateGetRequest(TokenAnswer $answer, RequestData $data, string $operation): GetRequestInfo
    {
        return new GetRequestInfo(
            type: $data->requestType,
            accessToken: $answer->token,
            operation: $operation,
            filename: $data->data['filename'],
//            filename: 'test.jpg',
            content: $data->data,
        );
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
