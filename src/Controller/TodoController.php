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
        $todos = array();
        for($i=0; $i < 10; $i++) {
            array_push($todos, [
                'id' => $i,
                'title' => 'Tache '.$i+1,
                'slug' => 'tache'
            ]);
        }
        return $this->render('todo/index.html.twig', [
            'tasks' => $todos
        ]);
    }

    #[Route('/todo/{slug}-{id}', name: 'todo.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id): Response
    {
        return $this->render('todo/show.html.twig', [
            'id' => $id,
            'slug' => $slug,
            'description' => 'Voir ici la description de la tache '.$id
        ]);
    }
}
