<?php

namespace App\Controllers;

use Framework\Response;
use Framework\ResponseFactory;
use Framework\Request;

class HomeController
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function index(Request $request): Response
    {
        return $this->responseFactory->view('index.html.twig', [
            'request' => $request
        ]);
    }

    public function profile(Request $request): Response
    {
        return $this->responseFactory->view('profile.html.twig', [
            'request' => $request
        ]);
    }

    public function dashboard(Request $request): Response
    {
        // Hier kun je later data uit de database ophalen voor je voortgang
        return $this->responseFactory->view('dashboard.html.twig', [
            'request' => $request
        ]);
    }

    public function faq(Request $request): Response
    {
        return $this->responseFactory->view('faq.html.twig', [
            'request' => $request
        ]);
    }

    public function blogIndex(Request $request): Response
    {
        return $this->responseFactory->view('blog.html.twig', ['request' => $request]);
    }

//    public function blogShow(Request $request): Response
//    {
//        $slug = $request->getAttribute('slug');
//
//        return $this->responseFactory->view("blogs/{$slug}.html.twig", ['request' => $request]);
//    }
}
