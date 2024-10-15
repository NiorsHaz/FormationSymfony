<?php

namespace App\Controller\API;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectApiController extends AbstractController
{
    #[Route("/api/projects", methods: "GET")]
    public function findAll(ProjectRepository $repository)
    {
        $projects = $repository->findAll();
        return $this->json($projects, 200, [], [
            'groups' => ['projects.index']
        ]);
    }

    #[Route("/api/projects/{id}", requirements: ['id' => Requirement::DIGITS])]
    public function findById(Project $project)
    {
        return $this->json($project, 200, [], [
            'groups' => ['projects.index', "projects.desc", "projects.task", "tasks.title"]
        ]);
    }


    // Creation avec groups (choisir les champs que l'utilisateur peut remplir)
    #[Route("/api/projects", methods: "POST")]
    public function create(Request $request, SerializerInterface $serializer)
    {
        $project = new Project();
        dd($serializer->deserialize($request->getContent(), Project::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $project,
            'groups' => ['projects.create']
        ]));
    }
}
