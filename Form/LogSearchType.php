<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class LogSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('term', 'search', array(
                'required' => false,
            ))
            ->add('level', 'choice', array(
                'choices'     => $options['log_levels'],
                'required'    => false
            ))
            ->add('results', 'text', array(
                'required' => false,
            ))
            ->add('datefrom', 'text', array(
                'required'    => true,
            ))
            ->add('dateto', 'text', array(
                'required'    => true,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'log_levels'      => array(),
                'csrf_protection' => false,
            ))
            ->setAllowedTypes(array(
                'log_levels'    => 'array',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'search';
    }
}
