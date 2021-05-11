<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CampusFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner le nom du campus',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le nom du campus doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('codePostal', null, [
                'label' => 'Code Postal',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner le code postal de la ville',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 10,
                        'minMessage' => 'Veuillez indiquer au minimum {{ limit }} caractères',
                        'maxMessage' => 'Le code postal de la ville doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;

        $builder->get('codePostal')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                if (strlen($form->getData()) >= 2) {
                    $this->addVilleField($form->getParent(), $form->getData());
                }
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                $ville = $data->getVille();
                $form = $event->getForm();
                if ($ville) {
                    $codePostal = $ville->getCodePostal();
                    $this->addVilleField($form, $codePostal);
                    $form->get('codePostal')->setData($codePostal);
                } else {
                    $this->addVilleField($form, '');
                }
            }
        );
    }

    private function addVilleField(FormInterface $form, ?string $codePostal)
    {
        if ($codePostal) {
            $form->add('ville', EntityType::class, [
                'class' => Ville::class,
                'label' => 'Ville',
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Choisir le code postal',
                'choice_label' => 'nom',
                'query_builder' => function(VilleRepository $villeRepository) use ($codePostal) {
                    return $villeRepository->getVillesByCodePostal($codePostal);
                },
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner la ville du campus',
                    ]),
                ],
            ]);
        }
        else {
            $form->add('ville', EntityType::class, [
                'class' => Ville::class,
                'label' => 'Ville',
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Choisir le code postal',
                'choice_label' => 'nom',
                'choices' => [],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner la ville du campus',
                    ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Campus::class,
        ]);
    }
}
