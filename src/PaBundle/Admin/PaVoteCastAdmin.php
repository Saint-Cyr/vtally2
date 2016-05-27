<?php

namespace PaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PaVoteCastAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('figureValue')
            ->add('pollingStation')
            ->add('wordValue')
            ->add('edited')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('figureValue')
            ->add('wordValue')
            ->add('edited')
            ->add('dependentCandidate')
            ->add('independentCandidate')
            ->add('pollingStation')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('figureValue')
            ->add('wordValue')
            ->add('dependentCandidate')
            ->add('pollingStation', 'sonata_type_model_autocomplete', 
                  array('property' => 'name', 'to_string_callback' => function($entity, $property){return $entity->getName();}))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('figureValue')
            ->add('wordValue')
            ->add('edited')
        ;
    }
}
