<?php

namespace App\Form;

use App\Entity\Images;
use App\Entity\Products;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu'
            ])
            ->add('stock', NumberType::class, [
                'label' => 'Stock'
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix'
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Catégories',
                'class' => Categories::class                
            ])
            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Products::class
        ]);
    }
}