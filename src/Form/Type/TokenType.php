<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('debug', CheckboxType::class, ['label' => 'Debug mode', 'required' => false])
        ->add('authKey', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Auth Key']])
        ->add('shopPath', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Shop Path']])
        ->add('save', SubmitType::class, ['label' => 'Connect'])
        ;
    }
}