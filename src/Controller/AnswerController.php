<?php

namespace App\Controller;

use App\Service\Model\ResponseInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/answer', name: '')]
class AnswerController extends AbstractController
{
    #[Route('getFile', name: 'answer_getFile')]
    public function getFile(Request $request): Response
    {
        return new Response(
            content: 'Success get file',
            status: 200
        );
    }

    #[Route('authError', name: 'answer_authError')]
    public function authError(Request $request): Response
    {
        return new Response(
            content: 'Error in auth',
            status: 500
        );
    }

    #[Route('fileNotExist', name: 'answer_fileNotExist')]
    public function fileNotExist(Request $request): Response
    {
        return new Response(
            content: 'Error! File not exist',
            status: 500
        );
    }

    #[Route('reqError', name: 'answer_reqError')]
    public function requestError(Request $request): Response
    {
        return new Response(
            content: 'Error! Request has invalid data',
            status: 500
        );
    }

    #[Route('uploadError', name: 'answer_uploadError')]
    public function uploadError(Request $request): Response
    {
        return new Response(
            content: 'Error! Upload not success',
            status: 500);
    }

    #[Route('saveFile', name: 'answer_saveFile')]
    public function saveFile(Request $request): Response
    {
        echo date('FY');
        return new Response(
            content: 'Success to save file',
            status: 200
        );
    }
}





