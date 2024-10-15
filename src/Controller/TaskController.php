<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Service\DeleteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task.index')]
    public function index(Request $request, TaskRepository $repository): Response
    {
        // Récupérer les valeurs de recherche et de filtre min/max
        $searchTitle = $request->query->get('search', '');
        $minEstimate = $request->query->get('min_estimate', 0);
        $maxEstimate = $request->query->get('max_estimate', 10000); // Limite par défaut

        // Appeler la méthode du repository pour filtrer les tâches
        $tasks = $repository->findByFilters($this->isGranted('ROLE_ADMIN'), $searchTitle, $minEstimate, $maxEstimate);
        $totalEstimates = $repository->findTotalEstimates($this->isGranted('ROLE_ADMIN'), $searchTitle, $minEstimate, $maxEstimate);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'totalEstimates' => $totalEstimates,
            'searchTitle' => $searchTitle,
            'minEstimate' => $minEstimate,
            'maxEstimate' => $maxEstimate,
        ]);
    }

    #[Route('/tasks/{slug}-{id}', name: 'task.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, TaskRepository $repository): Response
    {
        $task = $repository->find($id);

        if ($task->getSlug() !== $slug) {
            return $this->redirectToRoute('task.show', ['slug' => $task->getSlug(), 'id' => $task->getId()]);
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/edit', name: 'task.edit')]
    public function edit(Task $task, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Les informations ont bien été enregistrées');
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/tasks/create', name: 'task.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();
            $this->addFlash('success', 'Les informations ont bien été enregistrées');
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/{id}/delete', name: 'task.delete', methods: ['POST'])]
    public function delete(Task $task, DeleteService $deleteService): Response
    {
        // Si l'utilisateur est admin, on effectue une suppression hard
        if ($this->isGranted('ROLE_ADMIN')) {
            $deleteService->hardDelete($task);
            $this->addFlash('success', 'Tâche supprimée définitivement.');
        }
        // Sinon, on effectue une soft delete
        else {
            if ($task->isDeleted()) {
                throw new AccessDeniedException('Vous ne pouvez pas supprimer une tâche déjà supprimée.');
            }
            $deleteService->softDelete($task);
            $this->addFlash('success', 'Tâche supprimée temporairement.');
        }

        return $this->redirectToRoute('task.index');
    }

    #[Route('/tasks/paginate', name: 'task.paginate')]
    public function paginate(Request $request, TaskRepository $repository): Response
    {

        // Pagination
        $page = $request->query->getInt('page', 1);
        $limit = 2;

        // Paginator
        // $tasks =  $repository->paginateWithPaginatorTask($page, $limit);
        // $maxPage = ceil($tasks->count() / $limit);


        // KnpPaginatorBundle
        $tasks =  $repository->paginateTask($page, $limit);


        return $this->render('task/paginate.html.twig', [
            'tasks' => $tasks,
            //  'maxPage' => $maxPage,
            'page' => $page
        ]);
    }
}
