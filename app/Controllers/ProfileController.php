<?php

namespace App\Controllers;

use App\Repositories\ProfileRepositoryInterface;
use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;

class ProfileController
{
    private ResponseFactory $responseFactory;
    private ProfileRepositoryInterface $profileRepository;
    private AuthService $authService;
    private Session $session;

    public function __construct(
        ResponseFactory $responseFactory,
        ProfileRepositoryInterface $profileRepository,
        AuthService $authService,
        Session $session
    ) {
        $this->responseFactory = $responseFactory;
        $this->profileRepository = $profileRepository;
        $this->authService = $authService;
        $this->session = $session;
    }

    public function show(Request $request): Response
    {
        $profile = $this->profileRepository->get();
        return $this->responseFactory->view('profile.html.twig', [
            'request' => $request,
            'profile' => $profile,
            'session' => $this->session // <-- Add this line!
        ]);
    }

    public function edit(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $profile = $this->profileRepository->get();

        // Update this line to match your folder structure:
        return $this->responseFactory->view('profiles/edit.html.twig', [
            'request' => $request,
            'profile' => $profile,
            'session' => $this->session
        ]);
    }

    public function update(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $profile = $this->profileRepository->get();

        $profile->about_me = trim($request->get('about_me') ?? '');
        $profile->eager_to_learn = trim($request->get('eager_to_learn') ?? '');
        $profile->perseverance = trim($request->get('perseverance') ?? '');
        $profile->team_player = trim($request->get('team_player') ?? '');
        $profile->languages = trim($request->get('languages') ?? '');
        $profile->github_url = trim($request->get('github_url') ?? '');
        $profile->email = trim($request->get('email') ?? '');

        // Add validation if needed, similar to GradeController

        $this->profileRepository->update($profile);
        return $this->responseFactory->redirect('/profiles');
    }
}
