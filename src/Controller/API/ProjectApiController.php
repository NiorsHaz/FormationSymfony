<?php

namespace App\Controller\API;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProjectApiController extends AbstractController
{
    #[Route("/api/projects")]
    public function index(ProjectRepository $repository)
    {
        $projects = $repository->findAll();
        return $this->json($projects, 200, [], [
            'groups' => ['projects.index']
        ]);
    }
}
