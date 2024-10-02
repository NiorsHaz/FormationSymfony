<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TodoController extends AbstractController
{

    #[Route('/todo', name: 'todo.index')]
    public function index(Request $request): Response
    {
        return $this->render('todo/index.html.twig', [
            'tasks' => ['Tache 1', 'Tache 2', 'Tache 3']
        ]);
    }

    #[Route('/todo/{slug}-{id}', name: 'todo.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id): Response
    {
        return new Response('Tache n°' . $id . ' : ' . $slug);
    }
}
