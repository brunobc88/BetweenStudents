<?php

namespace App\Form;

use App\Entity\Campus;
use App\Services\SearchSortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchSortieFormType extends AbstractType
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
            ->add('dateMin', DateType::class, [
                'label' => 'Entre le ',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('dateMax', DateType::class, [
                'label' => 'et le ',
                'required' => false,
                'widget' => 'single_text',
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
