<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostModification;
use App\Entity\PostReaction;
use App\Form\CommentFormType;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/post', name: 'app_post_')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploader $fileUploader
    )
    {}

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();

        $post = new Post();
        $createPostForm = $this->createForm(PostType::class, $post);
        $createPostForm->handleRequest($request);

        if ($createPostForm->isSubmitted()  && $createPostForm->isValid()) {
            $post->setOwner($user);

            try {
                $this->addFlash('success', 'Your Post was successfully uploaded!');
                $this->entityManager->persist($post);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Failed to update the entity: ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/create.html.twig', [
            'user' => $user,
            'form' => $createPostForm,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(?Post $post): Response
    {
        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
            return $this->redirectToRoute('app_home');
        }

        $commentForm = $this->createForm(CommentFormType::class);

        return $this->render('post/show.html.twig', [
            'posts' => [$post],
            'user' => $this->getUser(),
            'commentForm' => $commentForm
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Post $post): Response
    {
        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
            return $this->redirectToRoute('app_home');
        }

        if ($post->getOwner() !== $this->getUser()) {
            $this->addFlash('danger', 'You cannot modify a post that is not yours');
            return $this->redirectToRoute('app_home');
        }

        $currentContent = $post->getContent();
        $restrictedUsers = $post->getPostAudience()?->getUsers();
        $updatePostForm = $this->createForm(PostType::class, $post, [
            'users' => $restrictedUsers
        ]);
        $updatePostForm->handleRequest($request);

        if ($updatePostForm->isSubmitted() && $updatePostForm->isValid()) {
            $post->setUpdatedAt(new \DateTimeImmutable());

            if ($post->getContent() !== $currentContent) {
                $postModification = new PostModification();
                $postModification->setPost($post);
                $postModification->setContent($currentContent);
                $post->addPostModification($postModification);
                $this->entityManager->persist($postModification);
            }

            try {
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Failed to update the entity: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Your Post was successfully updated!');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'user' => $this->getUser(),
            'form' => $updatePostForm
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(?Post $post): Response
    {
        if (!$post) {
            return new JsonResponse(
                ['error' => 'The requested Post is not found'],
                500
            );
        }

        $postReactions = $post->getPostReactions();
        $postAudience = $post->getPostAudience();
        $postModifications = $post->getPostModifications();

        if (!empty($postReactions->toArray())) {
            foreach ($postReactions as $reaction) {
                $this->entityManager->remove($reaction);
            }
        }

        if (!empty($postModifications->toArray())) {
            foreach ($postModifications as $modification) {
                $this->entityManager->remove($modification);
            }
        }

        if ($postAudience) {
            $this->entityManager->remove($postAudience);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        $this->addFlash('success', 'Your Post was successfully deleted!');
        return new JsonResponse(['message' => 'The Post has been deleted']);
    }

    #[Route('/{id}/react', name: 'react')]
    public function react(
        int $id,
        Request $request
    ): JsonResponse
    {
        $post = $this->entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
        }

        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $reaction = $data['reaction'];

        if ($reaction === 'like' || $reaction == 'dislike') {
            $postReaction = $this->entityManager->getRepository(PostReaction::class)
                ->findOneBy([
                    'post' => $post,
                    'owner' => $this->getUser()
                ]);

            if ($postReaction) {
                if ($reaction === $postReaction->getType()) {
                    $this->entityManager->remove($postReaction);
                } else {
                    $postReaction->setType($reaction);
                }
            } else {
                $postReaction = new PostReaction();

                $postReaction
                    ->setPost($post)
                    ->setOwner($this->getUser())
                    ->setType($reaction);

                $post->addPostReaction($postReaction);

                $this->entityManager->persist($postReaction);
            }

            $this->entityManager->flush();

            $response = [
                'message' => 'Reaction Updated!',
                'post' => $post->getId(),
                'id' => $id
            ];
        } else {
            $response = [
                'message' => 'Incorrect reaction',
                'data' => $data,
            ];
        }

        return new JsonResponse($response);
    }

    #[Route('/upload/images', methods: ['POST'])]
    public function uploadImages(
        Request $request,
    ): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'post', 'images');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Image upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/posts/images/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/videos', methods: ['POST'])]
    public function uploadVideos(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'post', 'videos');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Video upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/posts/videos/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/files', methods: ['POST'])]
    public function uploadFiles(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'post', 'files');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'File upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/posts/files/'. $fileUploadResult
        ]);
    }

    #[Route('/{id}/history', name: 'update_history')]
    public function editHistory(?Post $post): Response
    {
        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
            return $this->redirectToRoute('app_home');
        }

        $postModifications = $post->getPostModifications();

        return $this->render('post/update_history.html.twig', [
            'post' => $post,
            'postModifications' => $postModifications,
            'user' => $this->getUser()
        ]);
    }
}
