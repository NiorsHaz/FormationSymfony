<?php

namespace App\Controller\API;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class ProjectApiController extends AbstractController
{
    #[Route("/api/projects")]
    public function findAll(ProjectRepository $repository)
    {
        $projects = $repository->findAll();
        return $this->json($projects, 200, [], [
            'groups' => ['projects.index']
        ]);
    }

    #[Route("/api/projects/{id}", requirements: ['id' => Requirement::DIGITS])]
    public function findById(Project $project)
    {
        return $this->json($project, 200, [], [
            'groups' => ['projects.index', "projects.desc", "projects.task", "projects.title"]
        ]);
    }
}
