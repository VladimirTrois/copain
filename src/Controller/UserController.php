<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users', name: 'user_')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UserRepository $repo): JsonResponse
    {
        $users = $repo->findAll();

        return $this->json($users, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', ['groups' => 'user:write']);
        $em->persist($user);
        $em->flush();

        return $this->json($user, 201, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(Request $request, User $user, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $serializer->deserialize($request->getContent(), User::class, 'json', [
            'object_to_populate' => $user,
            'groups' => ['user:write'],
        ]);

        $em->flush();

        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return $this->json(null, 204);
    }
}
