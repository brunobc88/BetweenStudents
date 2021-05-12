<?php

namespace App\Form;

use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                    new NotBlank([
                        'message' => 'Veuillez renseigner la date de la sortie',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => "tomorrow",
                        'message' => 'La date de la sortie doit débutée au minimum demain',
                    ]),
                ],
            ])
            ->add('dateClotureInscription', DateTimeType::class, [
                'label' => 'Date de clôture des inscriptions',
                'required' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner la date de clotûre des inscriptions',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => "tomorrow",
                        'message' => 'La date de clôture des inscriptions doit débutée au minimum demain',
                    ]),
                    new Expression([
                        'expression' => 'value <= this.getParent()["dateDebut"].getData()',
                        'message' => 'La date de clôture des inscriptions doit être avant ou égal à la date de la sortie',
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
                        'min' => 2,
                        'max' => 10,
                        'minMessage' => 'Veuillez indiquer au minimum {{ limit }} caractères',
                        'maxMessage' => 'Le code postal du lieu de rendez-vous doit contenir au maximum {{ limit }} caractères',
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
                        'message' => 'Veuillez renseigner la ville du lieu de rendez-vous',
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
                        'message' => 'Veuillez renseigner la ville du lieu de rendez-vous',
                    ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
