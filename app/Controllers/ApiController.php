<?php

namespace App\Controllers;

use App\Repositories\GradeRepositoryInterface;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class ApiController
{
    private ResponseFactory $responseFactory;
    private GradeRepositoryInterface $gradeRepository;

    public function __construct(ResponseFactory $responseFactory, GradeRepositoryInterface $gradeRepository)
    {
        $this->responseFactory = $responseFactory;
        $this->gradeRepository = $gradeRepository;
    }

    /**
     * GET /api/grades
     * Returns all grades as a JSON collection.
     */
    public function grades(Request $request): Response
    {
        $grades = $this->gradeRepository->all();

        $data = array_map(fn($grade) => [
            'id'       => $grade->id,
            'quarter'  => $grade->quarter,
            'course'   => $grade->course,
            'ec'       => $grade->ec,
            'toetsing' => $grade->toetsing,
            'cijfer'   => $grade->cijfer,
            'status'   => $grade->status,
        ], $grades);

        return $this->responseFactory->json([
            'meta'  => ['resource' => 'grades', 'total' => count($data)],
            'links' => ['self' => '/api/grades'],
            'data'  => $data,
        ]);
    }

    /**
     * GET /api/grades/{id}
     * Returns a single grade as JSON, or a 404 error envelope.
     */
    public function grade(Request $request): Response
    {
        $id    = (int) $request->get('id');
        $grade = $this->gradeRepository->find($id);

        if ($grade === null) {
            return $this->responseFactory->json([
                'meta'  => ['resource' => 'grades'],
                'links' => ['self' => '/api/grades/' . $id, 'collection' => '/api/grades'],
                'error' => ['status' => 404, 'message' => 'Grade not found'],
            ], 404);
        }

        return $this->responseFactory->json([
            'meta'  => ['resource' => 'grades'],
            'links' => ['self' => '/api/grades/' . $id, 'collection' => '/api/grades'],
            'data'  => [
                'id'       => $grade->id,
                'quarter'  => $grade->quarter,
                'course'   => $grade->course,
                'ec'       => $grade->ec,
                'toetsing' => $grade->toetsing,
                'cijfer'   => $grade->cijfer,
                'status'   => $grade->status,
            ],
        ]);
    }
}