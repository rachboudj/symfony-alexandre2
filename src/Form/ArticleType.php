<?php

namespace App\Form;

use App\Entity\Articles;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('price')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('imageFile', VichImageType::class)
            ->add('status')
            ->add('categorys', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title',
                'multiple' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['is_edit'] ? 'Modifier l\'article' : 'Créer Article'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
            'is_edit' => false,
        ]);
    }
}
