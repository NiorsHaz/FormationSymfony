<?php

namespace App\Controller\API;

use App\Annotation\TokenRequired;
use App\Entity\Task;
use App\Repository\TaskRepository;
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

class TaskApiController extends AbstractController
{

    // *[CREATE]*

    #[Route("/api/tasks", methods: "POST")]
    #[TokenRequired]
    public function create(
        #[MapRequestPayload(serializationContext: [
            'groups' => ['tasks.create']
        ])] Task $task,
        EntityManagerInterface $em
    ) {
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setUpdatedAt(new \DateTimeImmutable());
        $em->persist($task);
        $em->flush();
        return $this->json($task, 200, [], [
            'groups' => ['tasks.show']
        ]);
    }

    // *[READ]*
    #[Route("/api/tasks", methods: "GET")]
    #[TokenRequired]
    public function findAll(TaskRepository $repository, Request $request)
    {
        // Récupérer les valeurs de recherche et de filtre min/max
        $searchTitle = $request->query->get('search', '');
        $minEstimate = $request->query->get('min_estimate', 0);
        $maxEstimate = $request->query->get('max_estimate', 10000); // Limite par défaut

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 100);

        // Appeler la méthode du repository pour filtrer les tâches
        $tasks =  $repository->paginateTask($this->isGranted('ROLE_ADMIN'), $searchTitle, $minEstimate, $maxEstimate, $page, $limit);

        // $tasks = $repository->findAll();
        return $this->json($tasks, 200, [], [
            'groups' => ['tasks.show']
        ]);
    }

    #[Route("/api/tasks/{id}", methods: "GET", requirements: ['id' => Requirement::DIGITS])]
    #[TokenRequired]
    public function findById(Task $task)
    {
        return $this->json($task, 200, [], [
            'groups' => ['tasks.show']
        ]);
    }

    // *[UPDATE]*

    #[Route("/api/tasks/{id}", methods: "PUT")]
    #[TokenRequired]
    public function update(
        int $id,
        Request $request,
        TaskRepository $repository,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
    ) {
        // Récupérer le projet existant
        $task = $repository->find($id);
        if (!$task) {
            throw new NotFoundHttpException('Task non trouvé');
        }

        // Désérialisation partielle en indiquant que les propriétés existantes de $project doivent être conservées
        $updatedTask = $serializer->deserialize(
            $request->getContent(),
            Task::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $task,  'groups' => ['tasks.update']]
        );

        $em->persist($updatedTask);
        $em->flush();
        return $this->json($updatedTask, 200, [], [
            'groups' => ['tasks.show']
        ]);
    }

    // *[DELETE]*

    #[Route("/api/tasks/{id}", methods: "DELETE")]
    #[TokenRequired]
    public function delete(
        int $id,
        DeleteService $deleteService,
        TaskRepository $repository,
    ) {
        // Récupérer le projet existant
        $task = $repository->find($id);
        if (!$task) {
            throw new NotFoundHttpException('Tasks non trouvé');
        }

        // Delete task
        $deleteService->softDelete($task);

        // Return no content code
        return new Response(null, 204);
    }
}
