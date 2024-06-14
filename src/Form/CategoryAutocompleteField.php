<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class CategoryAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Category::class,
            'placeholder' => 'Choose a Category',
            'choice_label' => function (?Category $category): string {
                return $category ? $category->getIcon() .' <span>'. $category->getName() .'</span>' : '';
            },
            'multiple' => true,
            'max_results' => 5,
            'min_characters' => 1,
            'query_builder' => function (EntityRepository $entityRepository): QueryBuilder {
                return $entityRepository->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
            }

            // choose which fields to use in the search
            // if not passed, *all* fields are used
            // 'searchable_fields' => ['name'],

            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
