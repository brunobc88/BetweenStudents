<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', null, [
                'label' => 'Pseudo',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre pseudo',
                    ]),
                    new Length([
                        'min' => 4,
                        'minMessage' => 'Votre pseudo doit contenir au minimum {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Votre pseudo doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre adresse email',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Votre adresse email doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('nom', null, [
                'label' => 'Nom',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Votre nom doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('prenom', null, [
                'label' => 'Prénom',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Votre prénom doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('telephone', null, [
                'label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 15,
                        'maxMessage' => 'Votre numéro de téléphone doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'label' => 'Campus',
                'required' => false,
                'choice_label' => 'nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre campus',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Avatar',
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
