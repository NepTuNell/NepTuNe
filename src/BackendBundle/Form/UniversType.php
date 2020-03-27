<?php

/**
 * author: CHU VAN Jimmy
 */
namespace BackendBundle\Form;

use BackendBundle\Form\ThemeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * FormType d'un univers
 */
class UniversType extends AbstractType
{
    /**
     * FormType des univers
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
     * Options du FormType des univers
     * 
     * @param OptionsResolver
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Univers'
        ));
    }

    /**
     * Préfixe du FormType lors du rendu de la vue
     * 
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_univers';
    }


}
