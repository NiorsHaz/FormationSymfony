<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task.index')]
    public function index(Request $request, TaskRepository $repository): Response
    {
        $tasks = $repository->findAll(4);
        $totalEstimates = $repository->findTotalEstimates(4);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'totalEstimates' => $totalEstimates
        ]);
    }

    #[Route('/tasks/{slug}-{id}', name: 'task.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
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

    #[Route('/tasks/{id}/edit', name: 'task.edit')]
    public function edit(Task $task, Request $request, EntityManagerInterface $em): Response
    {
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
}
