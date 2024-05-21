<?php

namespace App\Twig\Components;

use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class RegistrationForm extends AbstractController
{

    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function hasValidationErrors(): bool
    {
        return $this->getForm()->isSubmitted() && !$this->getForm()->isValid();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RegistrationFormType::class);
    }

    #[LiveAction]
    public function saveRegistration(): void
    {
        $this->submitForm();
        $this->addFlash('success', 'Your Account has been successfully implemented');
        $this->redirectToRoute('app_home');
    }
}