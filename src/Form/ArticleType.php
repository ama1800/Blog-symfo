<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ArticleType extends AbstractType
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'form-control my-2'
                ],
                'label' => 'Titre'
            ])
            ->add('content', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control my-2'
                ],
                'label' => 'Description'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'status',
                'choices' => [
                    'status.active' => Article::STATUS_ACTIVE,
                    'status.inactive' => Article::STATUS_INACTIVE,
                    'status.draft' => Article::STATUS_DRAFT,
                    'status.disabled' => Article::STATUS_DISABLED
                ],
                'attr' => [
                    'class' => 'form-control my-2'
                ],
            ])

            ->add('featuredImage', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control my-2'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                            'image/git',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Inserez une extension valide. Seulement(.png, .jpg, .jpeg, ou .git), maximum 1024Ko',
                    ])
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'attr' => [
                    'class' => 'form-control my-2'
                ],
            ])
        ;
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('createdAt', DateType::class, [
                    'label' => 'created_at',
                    'widget' => 'single_text',
                    'input'  => 'datetime_immutable'
                ])
                ->add('updaedAt', DateType::class, [
                    'label' => 'created_at',
                    'widget' => 'single_text',
                    'input'  => 'datetime_immutable'
                ])
                ->add('slug', null, ['label' => 'slug'])
            ;
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
