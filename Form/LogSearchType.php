<?php

namespace Mongolog\Bundle\MonologBrowserBundle\Form;

use MongoClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class LogSearchType extends AbstractType
{

    /**
     * @var \MongoDB $conn
     */
    private $conn;

    /**
     * @param \MongoClient $conn
     * @param $databaseName
     * @param $collectionName
     */
    public function __construct(\MongoClient $conn, $databaseName, $collectionName)
    {
        $this->conn = $conn->selectDB($databaseName)->createCollection($collectionName);
    }

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
            ->add('date_from', 'datetime', array(
                'date_widget' => 'single_text',
                'date_format' => 'yyyy/MM/dd',
//                'time_widget' => 'text',
                'required'    => false,
            ))
            ->add('date_to', 'datetime', array(
                'date_widget' => 'single_text',
                'date_format' => 'yyyy/MM/dd',
//                'time_widget' => 'text',
                'required'    => false,
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