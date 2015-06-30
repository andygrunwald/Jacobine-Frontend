<?php

namespace AppBundle\Form\Gerrit;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projectId', 'choice', ['choices' => $options['projects']])
            ->add('save', 'submit', array('label' => 'Generate statistics'));
    }

    public function getName()
    {
        return 'gerrit_project';
    }
}