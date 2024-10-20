<?php

namespace App\Controller\API;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskApiController extends AbstractController
{
    #[Route("/api/tasks")]
    public function findAll(TaskRepository $repository)
    {
        $tasks = $repository->findAll();
        return $this->json($tasks);
    }

    #[Route("/api/tasks/paginate")]
    public function findPaginateTask(TaskRepository $repository, Request $request)
    {
        // Récupérer les valeurs de recherche et de filtre min/max
        $searchTitle = $request->query->get('search', '');
        $minEstimate = $request->query->get('min_estimate', 0);
        $maxEstimate = $request->query->get('max_estimate', 10000); // Limite par défaut

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 100);
        
        $tasks =  $repository->paginateTask($this->isGranted('ROLE_ADMIN'), $searchTitle, $minEstimate, $maxEstimate, $page, $limit);
        return $this->json($tasks, 200, [], [
            'groups' => ['tasks.index',  "tasks.title"]
        ]);
    }
}
