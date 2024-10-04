<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/task', name: 'task.index')]
    public function index(Request $request, TaskRepository $repository): Response
    {
        $tasks = $repository->findAll(4);
        $totalEstimates = $repository->findTotalEstimates(4);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'totalEstimates' => $totalEstimates
        ]);
    }

    #[Route('/task/{slug}-{id}', name: 'task.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, TaskRepository $repository): Response
    {
        $task = $repository->find($id);

        if($task->getSlug() !== $slug) {
            return $this->redirectToRoute('task.show', ['slug' => $task->getSlug(), 'id' => $task->getId()]);
        }
        
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }
}
