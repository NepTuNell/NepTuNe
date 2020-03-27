<?php

/**
 * author: CHU VAN Jimmy
 */
namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType d'une section
 */
class SectionType extends AbstractType
{
    /**
     * FormType des sections
     * 
     * @param FormBuilderInterface
     * @param array
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle');
    }
    
    /**
     * Options du FormType des sections
     * 
     * @param OptionsResolver
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Section'
        ));
    }

    /**
     * Préfix du formulaire lors du rendu de la vue
     * 
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_section';
    }


}
