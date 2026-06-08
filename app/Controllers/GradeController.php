<?php

namespace App\Controllers;

use App\Models\Grade;
use App\Repositories\GradeRepositoryInterface;
use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;

class GradeController
{
    private ResponseFactory $responseFactory;
    private GradeRepositoryInterface $gradeRepository;
    private AuthService $authService;
    private Session $session;

    public function __construct(
        ResponseFactory $responseFactory,
        GradeRepositoryInterface $gradeRepository,
        AuthService $authService,
        Session $session
    ) {
        $this->responseFactory = $responseFactory;
        $this->gradeRepository = $gradeRepository;
        $this->authService     = $authService;
        $this->session         = $session;
    }

    public function index(Request $request): Response
    {
        $grades = $this->gradeRepository->all();

        $earnedEc = 0.0;
        foreach ($grades as $grade) {
            if ((int) $grade->status === 1) {
                $earnedEc += (float) $grade->ec;
            }
        }

        return $this->responseFactory->view('dashboard.html.twig', [
            'request'  => $request,
            'grades'   => $grades,
            'earnedEc' => $earnedEc,
        ]);
    }

    public function create(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        return $this->responseFactory->view('grades/create.html.twig', [
            'request' => $request,
        ]);
    }

    public function store(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $errors = [];

        $quarter  = trim($request->get('quarter') ?? '');
        $course   = trim($request->get('course') ?? '');
        $ec       = $request->get('ec');
        $toetsing = trim($request->get('toetsing') ?? '');
        $cijfer   = $request->get('cijfer');
        $status   = $request->get('status');

        if ($quarter === '') {
            $errors['quarter'] = 'Quarter is verplicht.';
        }
        if ($course === '') {
            $errors['course'] = 'Vak is verplicht.';
        }
        if (!is_numeric($ec) || (float)$ec <= 0) {
            $errors['ec'] = 'EC moet een positief getal zijn.';
        }
        if ($toetsing === '') {
            $errors['toetsing'] = 'Toetsvorm is verplicht.';
        }
        if ($cijfer !== null && $cijfer !== '' && (!is_numeric($cijfer) || (float)$cijfer < 1 || (float)$cijfer > 10)) {
            $errors['cijfer'] = 'Cijfer moet tussen 1 en 10 liggen.';
        }
        if (!in_array((int)$status, [0, 1, 2], true)) {
            $errors['status'] = 'Ongeldige status.';
        }

        $grade           = new Grade();
        $grade->quarter  = $quarter;
        $grade->course   = $course;
        $grade->ec       = (float)($ec ?? 0);
        $grade->toetsing = $toetsing;
        $grade->cijfer   = ($cijfer !== null && $cijfer !== '') ? (float)$cijfer : null;
        $grade->status   = (int)($status ?? 0);

        if (!empty($errors)) {
            return $this->responseFactory->view('grades/create.html.twig', [
                'request' => $request,
                'errors'  => $errors,
                'grade'   => $grade,
            ]);
        }

        $result = $this->gradeRepository->insert($grade);
        if ($result === null) {
            return $this->responseFactory->internalError();
        }

        $this->session->setFlash('success', 'Vak "' . $grade->course . '" is toegevoegd.');
        return $this->responseFactory->redirect('/dashboard');
    }

    public function edit(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id    = (int) $request->get('id');
        $grade = $this->gradeRepository->find($id);

        if ($grade === null) {
            return $this->responseFactory->notFound();
        }

        return $this->responseFactory->view('grades/edit.html.twig', [
            'request' => $request,
            'grade'   => $grade,
        ]);
    }

    public function update(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id    = (int) $request->get('id');
        $grade = $this->gradeRepository->find($id);

        if ($grade === null) {
            return $this->responseFactory->notFound();
        }

        $errors = [];

        $quarter  = trim($request->get('quarter') ?? '');
        $course   = trim($request->get('course') ?? '');
        $ec       = $request->get('ec');
        $toetsing = trim($request->get('toetsing') ?? '');
        $cijfer   = $request->get('cijfer');
        $status   = $request->get('status');

        if ($quarter === '') {
            $errors['quarter'] = 'Quarter is verplicht.';
        }
        if ($course === '') {
            $errors['course'] = 'Vak is verplicht.';
        }
        if (!is_numeric($ec) || (float)$ec <= 0) {
            $errors['ec'] = 'EC moet een positief getal zijn.';
        }
        if ($toetsing === '') {
            $errors['toetsing'] = 'Toetsvorm is verplicht.';
        }
        if ($cijfer !== null && $cijfer !== '' && (!is_numeric($cijfer) || (float)$cijfer < 1 || (float)$cijfer > 10)) {
            $errors['cijfer'] = 'Cijfer moet tussen 1 en 10 liggen.';
        }
        if (!in_array((int)$status, [0, 1, 2], true)) {
            $errors['status'] = 'Ongeldige status.';
        }

        $grade->quarter  = $quarter;
        $grade->course   = $course;
        $grade->ec       = (float)($ec ?? $grade->ec);
        $grade->toetsing = $toetsing;
        $grade->cijfer   = ($cijfer !== null && $cijfer !== '') ? (float)$cijfer : null;
        $grade->status   = (int)($status ?? $grade->status);

        if (!empty($errors)) {
            return $this->responseFactory->view('grades/edit.html.twig', [
                'request' => $request,
                'errors'  => $errors,
                'grade'   => $grade,
            ]);
        }

        $this->gradeRepository->update($grade);
        $this->session->setFlash('success', 'Vak "' . $grade->course . '" is bijgewerkt.');
        return $this->responseFactory->redirect('/dashboard');
    }

    public function deleteConfirm(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id    = (int) $request->get('id');
        $grade = $this->gradeRepository->find($id);

        if ($grade === null) {
            return $this->responseFactory->notFound();
        }

        return $this->responseFactory->view('grades/delete.html.twig', [
            'request' => $request,
            'grade'   => $grade,
        ]);
    }

    public function delete(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id    = (int) $request->get('id');
        $grade = $this->gradeRepository->find($id);

        if ($grade === null) {
            return $this->responseFactory->notFound();
        }

        $this->gradeRepository->delete($grade);
        $this->session->setFlash('success', 'Vak "' . $grade->course . '" is verwijderd.');
        return $this->responseFactory->redirect('/dashboard');
    }
}
