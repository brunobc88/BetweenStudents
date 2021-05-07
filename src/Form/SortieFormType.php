<?php

namespace App\Form;

use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner le nom de la sortie',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le nom de la sortie doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 4096,
                        'maxMessage' => 'La description de la sortie doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('nbreInscriptionMax', IntegerType::class, [
                'label' => 'Nombre inscription max',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner le nombre de participant',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 2,
                        'message' => 'Le nombre de participant doit être supérieure ou égale à {{ compared_value }}',
                    ]),
                ],
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date de la sortie',
                'required' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new NotNull([
                        'message' => 'Veuillez renseigner la date de la sortie',
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez renseigner la date de la sortie',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => "tomorrow",
                        'message' => 'La date de la sortie doit débutée après le {{ compared_value }}',
                    ]),
                ],
            ])
            ->add('dateClotureInscription', DateTimeType::class, [
                'label' => 'Date de cloture des inscriptions',
                'required' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new NotNull([
                        'message' => 'Veuillez renseigner la date de clotûre des inscriptions',
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez renseigner la date de clotûre des inscriptions',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => "tomorrow",
                        'message' => 'La date de clotûre des inscriptions doit débutée après le {{ compared_value }}',
                    ]),
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner la durée de la sortie',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 15,
                        'message' => 'La durée doit être supérieure ou égale à {{ compared_value }} min',
                    ]),
                ],
            ])
            ->add('rue', null, [
                'label' => 'Rue',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner la rue du lieu de rendez-vous',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'La rue du lieu de rendez-vous doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('codePostal', null, [
                'label' => 'Code Postal',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner le code postal du lieu de rendez-vous',
                    ]),
                    new Length([
                        'max' => 10,
                        'maxMessage' => 'Le code postal du lieu de rendez-vous doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'label' => 'Ville',
                'required' => false,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir le code postal',
                'choices' => [],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner la ville du lieu de rendez-vous',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Images',
                'required' => false,
                'mapped' => false,
            ])
            ->add('etat', CheckboxType::class, [
                'label' => 'Publiée',
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
