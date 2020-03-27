<?php

/**
 * author: CHU VAN Jimmy
 */
namespace BackendBundle\Form;

use BackendBundle\Entity\Backup;
use Symfony\Component\Form\AbstractType;
use BackendBundle\Repository\BackupRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Formulaire d'un backup
 */
class BackupType extends AbstractType
{
    /**
     * FormType des backups
     * 
     * @param FormBuilderInterface
     * @param array
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('backups', EntityType::class, [
            'class'    => Backup::class,
            'multiple' => false,
            'expanded' => true,
            'mapped'   => false,
            'query_builder' => function (BackupRepository $er) {
                return $er->fetchAllBackup();
            },
        ]);
    }
    
    /**
     * Options du FormType des backups
     * 
     * @param OptionsResolver
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Backup'
        ));
    }

    /**
     * Préfixe du formulaire
     * 
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_backup';
    }


}
