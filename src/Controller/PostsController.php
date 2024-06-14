<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\PostReaction;
use App\Entity\PostRestriction;
use App\Entity\User;
use App\Form\PostFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/post', name: 'app_post_')]
#[IsGranted('ROLE_USER')]
class PostsController extends AbstractController
{
    public function __construct(private readonly SluggerInterface $slugger, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();

        $post = new Post();
        $createPostForm = $this->createForm(PostFormType::class, $post);
        $createPostForm->handleRequest($request);

        if ($createPostForm->isSubmitted()  && $createPostForm->isValid()) {
            /* $post->setUser($user);
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your Post was successfully uploaded!');
            $this->redirectToRoute('app_home'); */

            /* $createPostFormRestrictions = $createPostForm->get('restrictions')->getData();
            if ($createPostFormRestrictions instanceof ArrayCollection) {
                foreach ()
            } */

            $post->setUser($user);

            try {
                $this->addFlash('success', 'Your Post was successfully uploaded!');
                $this->entityManager->persist($post);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Failed to update the entity: ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_home');

            // dd($post, $createPostForm->get('restriction')->getData());
        }

        return $this->render('posts/create.html.twig', [
            'user' => $user,
            'form' => $createPostForm,
        ]);
    }

    public function moveUploadedFile(Request $request, string $entity): string|FileException|bool
    {
        $file = $request->files->get('file');

        if ($file instanceof UploadedFile) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move($this->getParameter('posts_'.$entity.'_directory'), $newFilename);
                return $newFilename;
            } catch (FileException $e) {
                return $e;
            }
        }

        return false;
    }


    #[Route('/upload/images')]
    public function uploadImages(
        Request $request,
    ): Response
    {
        $fileUploadResult = $this->moveUploadedFile($request, 'images');

        if (is_string($fileUploadResult)) {
            return new JsonResponse([
                'link' => '/uploads/posts/images/'. $fileUploadResult
            ]);
        }

        else if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Image upload error'],
                500
            );
        }

        else {
            return new JsonResponse(
                ['error' => 'No Image uploaded'],
                400
            );
        }
    }

    #[Route('/upload/videos')]
    public function uploadVideos(Request $request): Response
    {
        $fileUploadResult = $this->moveUploadedFile($request, 'videos');

        if (is_string($fileUploadResult)) {
            return new JsonResponse([
                'link' => '/uploads/posts/videos/'. $fileUploadResult
            ]);
        }

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Video upload error'],
                500
            );
        }

        return new JsonResponse(
            ['error' => 'No Video uploaded'],
            400
        );
    }

    #[Route('/upload/files')]
    public function uploadFiles(Request $request): Response
    {
        $fileUploadResult = $this->moveUploadedFile($request, 'files');

        if (is_string($fileUploadResult)) {
            return new JsonResponse([
                'link' => '/uploads/posts/files/'. $fileUploadResult
            ]);
        }

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'File upload error'],
                500
            );
        }

        return new JsonResponse(
            ['error' => 'No File uploaded'],
            400
        );
    }

    #[Route('/update', name: 'update')]
    public function update(): Response
    {
        return new Response();
    }

    #[Route('/react/{id}/{type}', name: 'react')]
    public function react(
        #[MapEntity(exclude: ['type'])]
        ?Post $post,
        string $type
    ): Response
    {
        if (($type === 'like' || $type == 'dislike') && $post) {
            $postReaction = $this->entityManager->getRepository(PostReaction::class)
                ->findOneBy([
                    'post' => $post,
                    'user' => $this->getUser()
                ]);

            if ($postReaction) {
                if ($type === $postReaction->getType()) {
                    $this->entityManager->remove($postReaction);
                } else {
                    $postReaction->setType($type);
                }

                $this->entityManager->flush();
                return $this->redirectToRoute('app_home');
            }

            $postReaction = new PostReaction();

            $postReaction
                ->setPost($post)
                ->setUser($this->getUser())
                ->setType($type)
            ;

            $post->addPostReaction($postReaction);

            $this->entityManager->persist($postReaction);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/{id}', name: 'show')]
    public function show(Post $post): Response
    {
        return $this->render('posts/show.html.twig', [
            'post' => $post
        ]);
    }
}
