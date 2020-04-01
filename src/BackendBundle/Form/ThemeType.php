<?php

/**
 * author: CHU VAN Jimmy
 */
namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * FormType du thème
 */
class ThemeType extends AbstractType
{
    /**
     * FormType des themes
     * 
     * @param FormBuilderInterface
     * @param array
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class)
                ->add('subdivise', CheckboxType::class, [
                    'label' => "Diviser en section",
                    'attr'  => [ 
                        'style' => 'margin-right: 5%;'
                    ],
                    'label_attr' => [
                        'class' => "col-12 textColor"
                    ]
                ]);
    }
    
    /**
     * Options du FormType des themes
     * 
     * @param OptionsResolver
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Theme'
        ));
    }

    /**
     * Préfixe du FormType lors du rendu de la vue
     * 
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_theme';
    }


}
