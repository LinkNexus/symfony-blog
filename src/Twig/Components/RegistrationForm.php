<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class RegistrationForm extends AbstractController
{

    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?User $initialFormData = null;

    public function hasValidationErrors(): bool
    {
        return $this->getForm()->isSubmitted() && !$this->getForm()->isValid();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RegistrationFormType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        // $this->addFlash('success', 'Your Account has been successfully implemented');
    }
}