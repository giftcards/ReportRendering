<?php
namespace Yjv\ReportRendering\Datasource\Extension\Core\Type;

use Yjv\ReportRendering\Datasource\CallbackDatasource;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Yjv\ReportRendering\Datasource\DatasourceBuilderInterface;

use Yjv\ReportRendering\Datasource\AbstractDatasourceType;

class CallbackType extends AbstractDatasourceType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('callback'))
            ->setDefaults(array(
                'constructor' => function(DatasourceBuilderInterface $builder)
                {
                    return new CallbackDatasource(
                        $builder->getOption('callback'), 
                        $builder->getOption('params')
                    );
                },
                'params' => array()
            ))
            ->setAllowedTypes(array(
                'callback' => 'callable',
                'params' => 'array'
            ))
        ;
    }
    
    public function getName()
    {
        return 'callback';
    }
}
