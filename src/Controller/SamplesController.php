<?php

namespace App\Controller;

use http\Client;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/samples', name: 'samples')]
class SamplesController extends AbstractController
{

    public function homePage()
    {
        return null;
    }

    #[Route('/getfile/', name: '_getfile')]
    public function toGetFile(): Response
    {

        $body = [
            "type" => "image",
            "filename" => "students_bib.jpg",
            "operation" => "GET",
            'content' => []
        ];

//        $body = [
//            "type" => "document",
//            "filename" => "students_bib.docx",
//            "operation" => "GET",
//            'content' => []
//        ];

//        $body = [
//            "type" => "table",
//            "filename" => "students_bib.xlsx",
//            "operation" => "GET",
//            "content" => []
//        ];

        $request = new Request(content: json_encode($body));
        return $this->forward('App\Controller\MainYaPiController::edit',[
            "request" => $request
        ]);
    }

    #[Route('/edit/', name: '_edit')]
    public function toEdit(): Response
    {

        $body = [
            "type" => "table",
            "filename" => "students_bib.xlsx",
            "operation" => "EDIT",
            "content" => [
                'A10' => 'Hello world!'
            ]
        ];

        $request = new Request(content: json_encode($body));
        return $this->forward('App\Controller\MainYaPiController::edit',[
            "request" => $request
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/add/', name: '_add')]
    public function toAdd(): Response
    {

        $body = [
            "type" => "image",
            "filename" => "students_bib.jpg",
            "operation" => "UPLOAD",
            'content' => ['https://kartinkin.net/uploads/posts/2022-03/thumbs/1648121083_37-kartinkin-net-p-yarkie-krasochnie-kartinki-47.jpg']
        ];

//        $body = [
//            "type" => "document",
//            "filename" => "students_bib.docx",
//            "operation" => "UPLOAD",
//            'content' => [
//                'operations' => ['title', 'text', 'enter', 'text'],
//                'data' => [
//                    'Тестовый docx файл',
//                    'Есть функция, которая формирует массив.'.
//                    'В нем есть один элемент, который я хочу сохранить в БД.'.
//                    'Когда я сохраняю весь массив, то у меня он записывается в БД так:'.
//                    'Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 [4] => 5 [5] => 6 )'.
//                    'А мне нужно, чтобы записывалось так:'.
//                    '1 2 3 4 5 6'.
//                    'Подскажите как это можно сделать?',
//                    2,
//                    '2023'
//                ],
//            ]
//        ];

//        $body = [
//            "type" => "table",
//	        "filename" => "students_bib.xlsx",
//	        "operation" => "UPLOAD",
//	        "content" => [
//                ["Номер", "ФИО", "Статус", "Итоговая оценка"],
//			    ["1", "Павленко А.М.", "успешно", "7"],
//			    ["2", "Гойхман Г.Г.", "успешно", "8"],
//			    ["3", "Санников В.А.", "провал", "3"],
//        ]
//        ];

        $request = new Request(content: json_encode($body));
        return $this->forward('App\Controller\MainYaPiController::edit',[
            "request" => $request
        ]);
           // $this->redirectToRoute(route: 'app_yapi_edit')->setContent(json_encode($body));
    }
}