<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'code',
                TextType::class,
                ['required' =>
                    true,
                    'constraints' => [new Length(['max' => 30, 'min' => 3])],
                    ]
            )
            ->add('type', ChoiceType::class, ['choices'  => [
            'Бесплатный' => 0,
            'Покупка' => 1,
                'Аренда' => 2,
        ]])
            ->add(
                'name',
                TextType::class,
                ['required' =>
                    true,
                    'constraints' => [new Length(['max' => 50, 'min' => 3])],
                ]
            )
            ->add('description')
            ->add('cost');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
