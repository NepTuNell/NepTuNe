<?php

/**
 * author: CHU VAN Jimmy
 */

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 * FormType de l'image de profil
 */
class PictureProfileType extends AbstractType
{
    /**
     * FormType de l'image de profil
     * 
     * @param FormBuilderInterface
     * @param array
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('upload', FileType::class, [
                'label' => false,
                'error_bubbling' => true,
            ]
        );
    }
    
    /**
     * Options du FormType
     * 
     * @param OptionsResolver
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\PictureProfile'
        ));
    }

    /**
     * Préfix du formulaire rendu dans la vue
     * 
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'corebundle_pictureprofile';
    }


}
