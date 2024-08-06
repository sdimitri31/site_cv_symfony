<?php

namespace App\Form;

use App\Entity\Visit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ip')
            ->add('url')
            ->add('method')
            ->add('visitedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('visitedBy')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visit::class,
        ]);
    }
}
