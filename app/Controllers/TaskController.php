<?php

namespace App\Controllers;

use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class TaskController
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function index(): Response
    {
        return $this->responseFactory->view("tasks/index.html.twig");
    }

    public function create(): Response
    {
        return $this->responseFactory->view("tasks/create.html.twig");
    }

    public function show(Request $request): Response
    {

    }
}
