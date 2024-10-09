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
        // Récupérer les valeurs de recherche et de filtre min/max
        $searchTitle = $request->query->get('search', '');
        $minEstimate = $request->query->get('min_estimate', 0);
        $maxEstimate = $request->query->get('max_estimate', 10000); // Limite par défaut

        // Appeler la méthode du repository pour filtrer les tâches
        $tasks = $repository->findByFilters($searchTitle, $minEstimate, $maxEstimate);
        $totalEstimates = $repository->findTotalEstimates();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'totalEstimates' => $totalEstimates,
            'searchTitle' => $searchTitle,
            'minEstimate' => $minEstimate,
            'maxEstimate' => $maxEstimate,
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
