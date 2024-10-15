<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'project.index')]
    public function index(Request $request, ProjectRepository $repository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 1;
        $projects = $repository->paginateProjects($page, $limit);
        $maxPage = ceil($projects->count() / $limit);
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
            'maxPage' => $maxPage,
            'page' => $page
        ]);
    }
}
