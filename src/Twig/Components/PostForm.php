<?php

namespace App\Twig\Components;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

class PostForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?Post $initialFormData = null;

    public function hasValidationErrors(): bool
    {
        return $this->getForm()->isSubmitted() && !$this->getForm()->isValid();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(PostFormType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $this->addFlash('success', 'Your Post has been successfully created');
        $this->redirectToRoute('app_home');
    }
}