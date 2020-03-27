<?php

/**
 * author : CHU VAN Jimmy
 */
namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * FormType d'un commentaire
 */
class PostType extends AbstractType
{

    /**
     * FormType du commentaire
     * 
     * @param FormBuilderInterface
     * @param array
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('commentaire', TextareaType::class);
    }
    
    /**
     * Options du formulaire
     * 
     * @param OptionsResolver
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Post',
        ));
    }

    /**
     * Préfix du formulaire lors du rendu
     * 
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'corebundle_post';
    }

}
