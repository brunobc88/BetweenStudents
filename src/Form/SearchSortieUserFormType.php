<?php

namespace App\Form;

use App\Services\SearchSortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchSortieUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('checkbox', ChoiceType::class, [
                'label' => 'Sorties',
                'required' => false,
                'mapped' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'auxquelles je participe' => 'participe',
                    'que j\'organise' => 'organise',
                ],
                'placeholder' => 'Toutes',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchSortie::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
