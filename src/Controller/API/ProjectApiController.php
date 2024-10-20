<?php

namespace App\Controller\API;

use App\Annotation\TokenRequired;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\DeleteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectApiController extends AbstractController
{

    // *[CREATE]*

    // Création avec groups ,serialization et validator (MapRequestPayload)
    #[Route("/api/projects", methods: "POST")]
    #[TokenRequired]
    public function create(
        #[MapRequestPayload(serializationContext: [
            'groups' => ['projects.create']
        ])] Project $project,
        EntityManagerInterface $em
    ) {
        $em->persist($project);
        $em->flush();
        return $this->json($project, 200, [], [
            'groups' => ['projects.show']
        ]);
    }

    // *[READ]*

    #[Route("/api/projects", methods: "GET")]
    #[TokenRequired]
    public function findAll(ProjectRepository $repository, Request $request)
    {
        $searchTitle = $request->query->get('search', '');

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 100);
        
        $projects = $repository->paginateProjects($this->isGranted('ROLE_ADMIN'), $searchTitle, $page, $limit);
        return $this->json($projects, 200, [], [
            'groups' => ['projects.show']
        ]);
    }

    #[Route("/api/projects/{id}", methods: "GET", requirements: ['id' => Requirement::DIGITS])]
    #[TokenRequired]
    public function findById(Project $project)
    {
        return $this->json($project, 200, [], [
            'groups' => ['projects.show', 'projects.desc', "projects.task", "tasks.title"]
        ]);
    }

    // *[UPDATE]*

    #[Route("/api/projects/{id}", methods: "PUT")]
    #[TokenRequired]
    public function update(
        int $id,
        Request $request,
        ProjectRepository $repository,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
    ) {
        // Récupérer le projet existant
        $project = $repository->find($id);
        if (!$project) {
            throw new NotFoundHttpException('Projet non trouvé');
        }

        // Désérialisation partielle en indiquant que les propriétés existantes de $project doivent être conservées
        $updatedProject = $serializer->deserialize(
            $request->getContent(),
            Project::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $project,  'groups' => ['projects.update']]
        );

        $em->persist($updatedProject);
        $em->flush();
        return $this->json($updatedProject, 200, [], [
            'groups' => ['projects.show']
        ]);
    }

    // *[DELETE]*

    #[Route("/api/projects/{id}", methods: "DELETE")]
    #[TokenRequired]
    public function delete(
        int $id,
        DeleteService $deleteService,
        ProjectRepository $repository,
    ) {
        // Récupérer le projet existant
        $project = $repository->find($id);
        if (!$project) {
            throw new NotFoundHttpException('Projet non trouvé');
        }

        // Delete project
        $deleteService->softDelete($project);

        // Return no content code
        return new Response(null, 204);
    }
}
