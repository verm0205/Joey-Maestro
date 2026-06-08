<?php

namespace Tests\Unit\Controllers;

use App\Controllers\ApiController;
use App\Models\Grade;
use App\Repositories\GradeRepositoryInterface;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use PHPUnit\Framework\TestCase;

class ApiControllerTest extends TestCase
{
    private ResponseFactory $responseFactory;
    private GradeRepositoryInterface $gradeRepository;
    private ApiController $controller;

    protected function setUp(): void
    {
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->gradeRepository = $this->createMock(GradeRepositoryInterface::class);
        $this->controller      = new ApiController($this->responseFactory, $this->gradeRepository);
    }

    private function makeRequest(array $route = []): Request
    {
        $r = new Request('GET', '/api/grades', [], []);
        $r->routeParameters = $route;
        return $r;
    }

    private function makeGrade(int $id = 1): Grade
    {
        $g           = new Grade();
        $g->id       = $id;
        $g->quarter  = 'Q1';
        $g->course   = 'ITDP';
        $g->ec       = 3.0;
        $g->toetsing = 'Portfolio';
        $g->cijfer   = 7.5;
        $g->status   = 1;
        return $g;
    }

    public function testGradesReturnsJsonCollection(): void
    {
        $grade = $this->makeGrade();
        $this->gradeRepository->method('all')->willReturn([$grade]);

        $response = new Response('{}', 200);
        $this->responseFactory->expects($this->once())->method('json')->willReturn($response);

        $result = $this->controller->grades($this->makeRequest());
        $this->assertSame($response, $result);
    }

    public function testGradeReturnsFoundGrade(): void
    {
        $grade = $this->makeGrade(5);
        $this->gradeRepository->method('find')->with(5)->willReturn($grade);

        $response = new Response('{}', 200);
        $this->responseFactory->expects($this->once())->method('json')->willReturn($response);

        $result = $this->controller->grade($this->makeRequest(['id' => '5']));
        $this->assertSame($response, $result);
    }

    public function testGradeReturns404WhenNotFound(): void
    {
        $this->gradeRepository->method('find')->willReturn(null);

        $response = new Response('{}', 404);
        $this->responseFactory->expects($this->once())->method('json')
            ->with($this->arrayHasKey('error'), 404)
            ->willReturn($response);

        $result = $this->controller->grade($this->makeRequest(['id' => '99']));
        $this->assertSame($response, $result);
    }
}
