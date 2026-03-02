<?php

namespace App\Controllers;

use App\Repositories\TaskRepositoryInterface;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class TaskController
{
    private ResponseFactory $responseFactory;

    private TaskRepositoryInterface $taskRepository;

    public function __construct(ResponseFactory $responseFactory, TaskRepositoryInterface $taskRepository)
    {
        $this->responseFactory = $responseFactory;
        $this->taskRepository = $taskRepository;
    }

    public function index(): Response
    {
        $tasks = $this->taskRepository->all();
        return $this->responseFactory->view("tasks/index.html.twig", ["tasks" => $tasks]);
    }

    public function create(): Response
    {
        return $this->responseFactory->view("tasks/create.html.twig");
    }

    public function show(Request $request): Response
    {
        $taskId = (int)$request->get('id');
        $task = $this->taskRepository->find($taskId);

        if ($task === null) {
            return $this->responseFactory->notFound();
        }
        return $this->responseFactory->view("tasks/show.html.twig", ["task" => $task]);
    }
}
