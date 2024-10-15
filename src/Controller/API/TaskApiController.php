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
        $page = $request->query->getInt('page', 1);
        $tasks = $repository->paginateTask($page);
        return $this->json($tasks, 200, [], [
            'groups' => ['tasks.index',  "tasks.title"]
        ]);
    }
}
