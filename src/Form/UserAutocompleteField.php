<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class UserAutocompleteField extends AbstractType
{
    public function __construct(private readonly Security $security)
    {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => User::class,
            'placeholder' => 'Choose a User',
            'choice_label' => function (?User $user): string
                {
                    return $user ? '<img src= "/images/'. strtolower($user->getGender()) .'_icon.jpg" /> <span>'. $user->getUsername() .'</span>': '';
                },
            'multiple' => true,
            'query_builder' => function (EntityRepository $entityRepository): QueryBuilder {
                return $entityRepository->createQueryBuilder('u')
                    ->orderBy('u.username', 'ASC')
                    ->where('u.username != :username')
                    ->setParameter('username', $this->security->getUser()->getUsername())
                    ;
            },

            // choose which fields to use in the search
            // if not passed, *all* fields are used
            'searchable_fields' => ['username'],

            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
