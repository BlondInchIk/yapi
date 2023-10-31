<?php

namespace App\Controller;

use App\Controller\Models\RequestData;
use App\Controller\Models\TokenAnswer;
use App\Service;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


#[Route('/yapi', name: '')]
class MainYaPiController extends AbstractController
{
    private Service\RequestHandlerInterface $requestHandler;

    /**
     * @throws \Exception
     */
    public function __construct(private readonly Service\YandexService $yandexService)
    {
        $this->requestHandler = Service\RequestHandlerService::get();
    }

//<html>
//<body>
/*<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">*/
//<h1>YaPi</h1>
//<label for="name">Имя файла: </label><input type="text" id="name" name="name">
//<br><br>
//<label for="name">Тип файла: </label><input type="text" id="type" name="type">
//<br><br>
//<label for="name">Операция: </label><input type="text" id="operation" name="operation">
//<br><br>
//<input type="text" id="data" name="data" placeholder="Введите данные запроса">
//<input type="submit" value="Submit">
//</form>
//</html>
//<?php
//
//if ($_SERVER["REQUEST_METHOD"] == "POST") {
//$name = $_POST["name"];
//$type = $_POST["type"];
//$operation = $_POST["operation"];
//$data = $_POST["data"];
//$result = array('name' => $name, 'type' => $type, 'operation' => $operation, 'data' => $data);
//file_get_contents("http://127.0.0.1:8000/yapi/getfile/".implode('/', $result), true);
//}
//?

    #[Route('/getfile/', name: 'app_yapi_getfile')]
    public function getFile(Request $request, LoggerInterface $logger): Response
    {
        try {
            $session_id = microtime(true);
            $data = ['data' => "Hello world"];
            $dataRequest = new RequestData(
                session_id: $session_id,
                requestType: $data['type'],
                data: $data
            );
            $service = $this->yandexService;

            $cache = new FilesystemAdapter('', 1000, "cache");
            $productsCount = $cache->getItem(strval($session_id));
            $productsCount->set($dataRequest);
            $cache->save($productsCount);

            return $this->redirect('https://oauth.yandex.ru/authorize?response_type=code&client_id=d0f7bbb0a1314dea9fb5db275b64fcd5&state=' . strval($session_id));
        } catch (\Exception $exception) {
            return $this->redirectToRoute(route: "app_yapi_answer_authError");
        }
    }

    #[Route('/edit/', name: 'app_yapi_edit')]
    public function edit(Request $request): Response
    {
        try{
            $session_id = microtime(true);
            $data = json_decode($request->getContent(), true);
            $dataRequest = new RequestData(
                session_id: $session_id,
                requestType: $data['type'],
                data: $data
            );

            $cache = new FilesystemAdapter('', 1000, "cache");
            $productsCount = $cache->getItem(strval($session_id));
            $productsCount->set($dataRequest);
            $cache->save($productsCount);

            return $this->redirect('https://oauth.yandex.ru/authorize?response_type=code&client_id=d0f7bbb0a1314dea9fb5db275b64fcd5&state=' . strval($session_id));
        } catch (\Exception $exception){
            echo $exception->getMessage();
            return new Response();
//            return $this->redirectToRoute(route: "app_yapi_answer_authError");
        }
    }

    #[Route('/main/', name: 'app_yapi_main')]
    public function main(LoggerInterface $logger, Request $request): Response
    {
        try {
            $client_id = 'd0f7bbb0a1314dea9fb5db275b64fcd5';
            $client_secret = 'd926f15f8a2b4f289e30b67984d31ac4';
            $tokenAnswer = $this->getTokenAnswer(
                client_id: $client_id,
                client_secret: $client_secret
            );

            $cache = new FilesystemAdapter('', 0, "cache");
            $productsCount = $cache->getItem($tokenAnswer->session_id);
            $productsCount->get();

            $requestType =  $productsCount->get()->requestType;
            $operation = $productsCount->get()->data['operation'];
            $data =  $productsCount->get();
            $requestInfo = $this->requestHandler->generateGetRequest(answer: $tokenAnswer, data: $data, operation: $operation);
            $responseInfo = $this->yandexService->documentsRequest($requestInfo);
            $redirectRoute = match ($responseInfo->status){
                "Success" => $this->successResult($responseInfo),
                "Error" => $this->errorResult($responseInfo)
            };
            if ($responseInfo->status=="Success" && $responseInfo->operation="GET"){
                return $this->redirectToRoute(route: $redirectRoute);
            } elseif ($responseInfo->data == null){
                echo 'Файл не существует!';
                return new Response();
            } else {
                return $this->redirectToRoute(route: $redirectRoute);
            }
        } catch (\Exception $exception){
            echo $exception->getMessage();
            return new Response();
//            return $this->redirectToRoute(route: 'app_yapi_answer_uploadError');
        }

    }

    private function errorResult(Service\Model\ResponseInfo $responseInfo): string{
        return match ($responseInfo->operation){
            "UPLOAD" => 'answer_uploadError',
            "GET" => 'answer_fileNotExist',
            default => 'answer_reqError'
        };
    }

    private function successResult(Service\Model\ResponseInfo $responseInfo): string{
        return match ($responseInfo->operation){
            "EDIT", "UPLOAD"=> 'answer_saveFile',
            "GET" => 'answer_getFile',
        };
    }

    private function getTokenAnswer( string $client_id, string $client_secret): TokenAnswer
    {
        $query = array(
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'client_id' => $client_id,
            'client_secret' => $client_secret
        );
        $query = http_build_query($query);

        // Формирование заголовков POST-запроса
        $header = "Content-type: application/x-www-form-urlencoded";

        // Выполнение POST-запроса и вывод результата
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => $header,
                'content' => $query
            )
        );
        $context = stream_context_create($opts);
        $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);
        $result = json_decode($result);

        $session_id = $_REQUEST['state'];
        $token = $result->access_token;

        return new TokenAnswer(
            session_id: $session_id,
            token: $token
        );
    }
}
