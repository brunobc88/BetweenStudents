<?php

namespace App\Form;

use App\Entity\Campus;
use App\Services\SearchUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('isAdmin', CheckboxType::class, [
                'label' => 'Admin',
                'required' => false,
            ])
            ->add('isActif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
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

    public function getBlockPrefix()
    {
        return '';
    }
}
