<?php

namespace BackendBundle\Form;

use BackendBundle\Entity\Backup;
use Symfony\Component\Form\AbstractType;
use BackendBundle\Repository\BackupRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BackupType extends AbstractType
{
    /**
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Backup'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_backup';
    }


}
