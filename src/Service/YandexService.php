<?php

namespace App\Service;

use App\Service\Model\GetRequestInfo;
use App\Service\Model\ResponseInfo;
use Arhitector\Yandex\Disk as disk;
use http\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//Примеры запросов находятся внизу этого файла

//Класс обработки запросов
class YandexService {

    //Выбор типа файла
    function documentsRequest(GetRequestInfo $requestInfo):  ResponseInfo
    {
        return match ($requestInfo->type){
            'document' =>  $this->document($requestInfo),
            'table' => $this->table($requestInfo),
            'image' => $this->image($requestInfo),
        };
//        "Успех! Выполнена операция "
//            .$requestInfo->operation .' над файлом '
//            .$requestInfo->filename.' типа '
//            .$requestInfo->type;
    }

    //Обработка запросов при docx
    private function document(GetRequestInfo $requestInfo): ResponseInfo
    {
        //Скачивание файла
        if ($requestInfo->operation == 'GET'){
            try {
                $disk = new disk($requestInfo->accessToken);
                $resource = $disk->getResource('app:/'.$requestInfo->filename);
                $resource->has();
                $resource->download($requestInfo->filename, true);

                return new ResponseInfo(
                    data: file($requestInfo->filename),
                    type: $requestInfo->type,
                    operation: "GET"
                );

            } catch (\Exception $exception){
                return new ResponseInfo(
                    data: null,
                    type: $requestInfo->type,
                    operation: "GET",
                    status: "Error"
                );
            }
        }

        //Загрузка файла
        if ($requestInfo->operation == 'UPLOAD'){
            try {
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $phpWord->setDefaultParagraphStyle(
                    [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
                    ]
                );
                $section = $phpWord->addSection();
                for ($i = 0; $i < count($requestInfo->content['content']['operations']); $i += 1)
                {
                    if($requestInfo->content['content']['operations'][$i] == 'title')
                        $section->addText($requestInfo->content['content']['data'][$i], array('name' => 'Times New Roman', 'size' => 20, 'color' => '2F4F4F'));

                    if($requestInfo->content['content']['operations'][$i] == 'text')
                        $section->addText($requestInfo->content['content']['data'][$i], array('name' => 'Times New Roman', 'size' => 14));

                    if($requestInfo->content['content']['operations'][$i] == 'enter')
                        $section->addTextBreak($requestInfo->content['content']['data'][$i]);
                }

                $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                $objWriter->save($requestInfo->filename);

                $disk = new disk($requestInfo->accessToken);
                $resource = $disk->getResource('app:/'.$requestInfo->filename);
                $resource->has();
                $resource->upload(dirname(dirname(__DIR__)).'/public/'.$requestInfo->filename, true);

                return new ResponseInfo(
                    data: ["filename" => $requestInfo->filename],
                    type: $requestInfo->type,
                    operation: "UPLOAD"
                );

            } catch (\Exception $exception){
                return new ResponseInfo(
                    data: null,
                    type: $requestInfo->type,
                    operation: "UPLOAD",
                    status: "Error"
                );
            }

        }

        //Редактирование файла
        if ($requestInfo->operation == 'EDIT'){
            return new ResponseInfo(
                data: null,
                type: $requestInfo->type,
                operation: "EDIT",
                status: "Error");
        }

        return new ResponseInfo(
            data: null,
            type: $requestInfo->type,
            operation: "Operation doesn't exist!",
            status: "Error");
    }

    //Обработка запросов при xlsx
    private function table(GetRequestInfo $requestInfo): ResponseInfo
    {

        //Скачивание файла
        if ($requestInfo->operation == 'GET'){
            try {
                $disk = new disk($requestInfo->accessToken);
                $resource = $disk->getResource('app:/'.$requestInfo->filename);
                $resource->has();
                $resource->download($requestInfo->filename, true);

                return new ResponseInfo(
                    data: ["filename" => $requestInfo->filename],
                    type: $requestInfo->type,
                    operation: "GET"
                );

            } catch (\Exception $exception){
                return new ResponseInfo(
                    data: null,
                    type: $requestInfo->type,
                    operation: "GET",
                    status: "Error"
                );
            }
        }

        //Загрузка файла
        if ($requestInfo->operation == 'UPLOAD'){
            try {
                $spreadsheet = new Spreadsheet();
                $spreadsheet->getActiveSheet()->fromArray($requestInfo->content['content'], null, 'A1', true);
                $writer = new Xlsx($spreadsheet);
                $writer->save($requestInfo->filename);

                $disk = new disk($requestInfo->accessToken);
                $resource = $disk->getResource('app:/'.$requestInfo->filename);
                $resource->has();
                $resource->upload(dirname(dirname(__DIR__)) . '/public/' . $requestInfo->filename, true);

                return new ResponseInfo(
                    data: ["filename" => $requestInfo->filename],
                    type: $requestInfo->type,
                    operation: "UPLOAD"
                );

            } catch (\Exception $exception){
                return new ResponseInfo(
                    data: ["exception" => $exception],
                    type: $requestInfo->type,
                    operation: "UPLOAD",
                    status: "Error"
                );
            }
        }

        //Редактирование файла с диска
        if ($requestInfo->operation == 'EDIT'){
            try {
                $disk = new disk($requestInfo->accessToken);
                $resource = $disk->getResource('app:/'.$requestInfo->filename);
                $resource->has();
                $resource->download($requestInfo->filename, true);

                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(TRUE);
                $spreadsheet = $reader->load($requestInfo->filename);
                $sheet = $spreadsheet->getActiveSheet();
                foreach ($requestInfo->content['content'] as $cell => $value)
                    $sheet->setCellValue($cell, $value);
                $writer = new Xlsx($spreadsheet);
                $writer->save($requestInfo->filename);

                $resource->upload(dirname(dirname(__DIR__)) . '/public/' . $requestInfo->filename, true);

                return new ResponseInfo(
                    data: ["filename" => $requestInfo->filename],
                    type: $requestInfo->type,
                    operation: "EDIT"
                );

            }  catch (\Exception $exception){
                return new ResponseInfo(
                    data: null,
                    type: $requestInfo->type,
                    operation: "EDIT",
                    status: "Error"
                );
            }
        }

        return new ResponseInfo(
            data: null,
            type: $requestInfo->type,
            operation: "Operation doesn't exist!",
            status: "Error");
    }

    private function image(GetRequestInfo $requestInfo): ResponseInfo
    {
        //Скачивание файла
        if ($requestInfo->operation == 'GET'){
            try {
                $disk = new disk($requestInfo->accessToken);
                $resource = $disk->getResource('app:/'.$requestInfo->filename);
                $resource->has();
                $resource->download($requestInfo->filename, true);

                return new ResponseInfo(
                    data: ["filename" => $requestInfo->filename],
                    type: $requestInfo->type,
                    operation: "EDIT"
                );

            } catch (\Exception $exception){
                return new ResponseInfo(
                    data: null,
                    type: $requestInfo->type,
                    operation: "GET",
                    status: "Error"
                );
            }
        }

        //Загрузка файла через url
        if ($requestInfo->operation == 'UPLOAD'){

            copy($requestInfo->content['content'][0],$requestInfo->filename);

            $disk = new disk($requestInfo->accessToken);
            $resource = $disk->getResource('app:/'.$requestInfo->filename);
            $resource->has();
            $resource->upload($requestInfo->filename, true);

            return new ResponseInfo(
                data: ["filename" => $requestInfo->filename],
                type: $requestInfo->type,
                operation: "UPLOAD"
            );
        }

        return new ResponseInfo(
            data: null,
            type: $requestInfo->type,
            operation: "Operation dont exist!",
            status: "Error");
    }

}

//Полностью готовый запрос, пример:
//$UserRequest = new Service\Model\GetRequestInfo(
//    type: 'docx',
//    accessToken: 'y0_AgAAAABSDRUBAADLWwAAAADbkvpJZA7hjiPpR4i-v4R4RPKJ25vBgL0',
//    operation: 'UPLOAD',
//    filename: 'test.docx',
//    content: [
//        'operations' => ['title', 'text', 'enter', 'text'],
//        'data' => [
//            'Тестовый docx файл',
//            'Есть функция, которая формирует массив.'.
//            'В нем есть один элемент, который я хочу сохранить в БД.'.
//            'Когда я сохраняю весь массив, то у меня он записывается в БД так:'.
//            'Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 [4] => 5 [5] => 6 )'.
//            'А мне нужно, чтобы записывалось так:'.
//            '1 2 3 4 5 6'.
//            'Подскажите как это можно сделать?',
//            2,
//            '2023'
//        ],
//    ]
//);

//
//        $UserRequest = new Service\Model\GetRequestInfo(
//            type: 'image',
//            accessToken: 'y0_AgAAAABSDRUBAADLWwAAAADbkvpJZA7hjiPpR4i-v4R4RPKJ25vBgL0',
//            operation: 'UPLOAD',
//            filename: '1648121083_37-kartinkin-net-p-yarkie-krasochnie-kartinki-47.jpg',
//            content: ['https://kartinkin.net/uploads/posts/2022-03/thumbs/1648121083_37-kartinkin-net-p-yarkie-krasochnie-kartinki-47.jpg'],
//        );

//Данные типа EDIT xlsx
//Пример Edit xlsx
//content: [
//    'B1' => 'Hello, PhpSpredsheet!',
//    'B2' => 'Hello, Myrusakov!',
//    'B3' => 'Open please, this message'
//];
//
//Пример Upload docx
//content:[
//    ['name', 'id', 'value'],
//    ['Ivan', '1', '84352345'],
//]
//
//Пример UPLOAD docx
//content: [
//'operations' => ['title', 'text', 'enter', 'text'],
//'data' => [
//    'Тестовый docx файл',
//    'Есть функция, которая формирует массив.'.
//    'В нем есть один элемент, который я хочу сохранить в БД.'.
//    'Когда я сохраняю весь массив, то у меня он записывается в БД так:'.
//    'Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 [4] => 5 [5] => 6 )'.
//    'А мне нужно, чтобы записывалось так:'.
//    '1 2 3 4 5 6'.
//    'Подскажите как это можно сделать?',
//    2,
//    '2023'
//    ],
//]