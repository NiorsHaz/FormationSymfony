<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'project:index')]
    public function index(ProjectRepository $repository): Response
    {
        dd($repository->findAllWithTaskCount());
        return $this->render('project/index.html.twig', [

        ]);
    }
}
