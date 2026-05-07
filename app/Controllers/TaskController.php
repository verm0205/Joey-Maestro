<?php

namespace App\Controllers;

use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use DateTime;
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

    public function store(Request $request): Response
    {
        $title = $request->get('title');
        $description = $request->get('description') ?? '';
        $priority = $request->get('priority');
        $status = $request->get('status');
        $createdAt = $request->get('created_at');

        $errors = [];
        if ($title === null || trim($title) === '') {
            $errors['title'] = "Title is required.";
            $title = null;
        }

        if (!is_numeric($priority) || in_array((int)$priority, range(0, 3)) === false) {
            $errors['priority'] = "Priority must be specified.";
            $priority = null;
        }

        if (!is_numeric($status) || in_array((int)$status, range(0, 4)) === false) {
            $errors['status'] = "Status must be specified.";
            $status = null;
        }

        if ($createdAt !== null) {
            $createdAt = DateTime::createFromFormat('Y-m-d', $createdAt);
            if ($createdAt) {
                $createdAt = $createdAt->getTimestamp();
            } else {
                $createdAt = time();
            }
        }

        $task = new Task();
        $task->title = $title ?? '';
        $task->description = $description;
        $task->priority = (int)$priority;
        $task->status = (int)$status;
        $task->createdAt = (int)$createdAt;

        if (!empty($errors)) {
            return $this->responseFactory->view("tasks/create.html.twig", ["errors" => $errors, "task" => $task]);
        }

        $task = $this->taskRepository->insert($task);
        if ($task === null) {
            return $this->responseFactory->internalError();
        }
        return $this->responseFactory->redirect('/tasks/' . $task->id);
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

    public function edit(Request $request): Response
    {
        $taskId = (int)$request->get('id');
        $task = $this->taskRepository->find($taskId);

        if ($task === null) {
            return $this->responseFactory->notFound();
        }

        // Only process the update if the user submitted the form (POST)
            $task->title = $request->get('title') ?? $task->title;
            $task->description = $request->get('description') ?? '';
            $task->priority = (int)$request->get('priority');
            $task->status = (int)$request->get('status');

        // Otherwise, just show the form
        return $this->responseFactory->view("tasks/edit.html.twig", ["task" => $task]);
    }

    public function delete(Request $request): Response
    {
        $taskId = (int)$request->get('id');
        $task = $this->taskRepository->find($taskId);

        if ($task === null) {
            return $this->responseFactory->notFound();
        }

            $this->taskRepository->delete($task);

        return $this->responseFactory->view("tasks/delete.html.twig", ["task" => $task]);
    }
}
