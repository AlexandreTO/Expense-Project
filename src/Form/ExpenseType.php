<?php

namespace App\Form;

use App\Entity\Expense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType ;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Food' => 'food',
                    'Entertainment' => 'entertainment',
                    'Healthcare' => 'healthcare',
                    'Other' => 'other',
                ],
                'label' => 'Category',
                'placeholder' => 'Select a Category',
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Amount',
                'scale' => 2,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text', // This makes it easier to use with HTML5 date inputs
                'label' => 'Date', 
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
        ]);
    }
}
