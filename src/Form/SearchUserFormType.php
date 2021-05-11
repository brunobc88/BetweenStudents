<?php

namespace App\Form;

use App\Entity\Campus;
use App\Services\SearchUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('keyword', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher par mots-clés'
                ]
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'label' => false,
                'required' => false,
                'choice_label' => 'nom',
                'placeholder' => 'Campus'
            ])
            ->add('isAdmin', ChoiceType::class, [
                'label' => 'Admin',
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Oui' => 'oui',
                    'Non' => 'non',
                ],
                'placeholder' => 'Peu importe',
            ])
            ->add('isActif', ChoiceType::class, [
                'label' => 'Actif',
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Oui' => 'oui',
                    'Non' => 'non',
                ],
                'placeholder' => 'Peu importe',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Rechercher',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchUser::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
