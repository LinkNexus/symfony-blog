<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use function PHPUnit\Framework\callback;

#[Route(path: '/comment', name: 'app_comment_')]
class CommentController extends AbstractController
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly EntityManagerInterface $entityManager
    )
    {}

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        Request $request
    ): JsonResponse
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $content = $data['content'];

        $post = $this->entityManager->getRepository(Post::class)->find($data['post_id']);

        $comment = new Comment();
        $comment
            ->setPost($post)
            ->setOwner($this->getUser())
            ->setContent($content);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Comment created!']);
    }

    #[Route('/fetch', name: 'fetch')]
    public function fetchComments(Request $request): JsonResponse
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $condition = $data['condition'];

        $post = $this->entityManager->getRepository(Post::class)->find($data['post_id']);

        $comments = match ($condition) {
            "newest" => $this->entityManager->getRepository(Comment::class)
                ->findBy(
                    ['post' => $post],
                    ['createdAt' => 'DESC']
                ),
            default => $this->entityManager->getRepository(Comment::class)->findBy(['post' => $post]),
        };

        return new JsonResponse($comments);
    }

    #[Route('/upload/images', methods: ['POST'])]
    public function uploadImages(
        Request $request,
    ): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'comment', 'images');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Image upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/comments/images/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/videos', methods: ['POST'])]
    public function uploadVideos(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'comment', 'videos');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Video upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/comments/videos/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/files', methods: ['POST'])]
    public function uploadFiles(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'comment', 'files');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'File upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/comments/files/'. $fileUploadResult
        ]);
    }
}
