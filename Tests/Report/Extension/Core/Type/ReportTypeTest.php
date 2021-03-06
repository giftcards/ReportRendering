<?php
namespace Yjv\ReportRendering\Tests\Report\Extension\Core\Type;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Yjv\ReportRendering\Report\ReportBuilder;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\OptionsResolver\Options;

use Yjv\ReportRendering\IdGenerator\ConstantValueIdGenerator;

use Yjv\ReportRendering\Report\Extension\Core\Type\ReportType;

use Mockery;

class ReportTypeTest extends TypeTestCase
{
    protected $options;
    
    public function setUp()
    {
        parent::setUp();
        $this->type = new ReportType();

    }
    
    public function testBuildReport()
    {
        $this->initializeForBuildTest();
        $this->type->buildReport($this->builder, $this->options);
    }
    
    public function testBuildReportWithEmptyDatasource()
    {
        $this->initializeForBuildTest();
        $this->options['datasource'] = array(null, array());
        
        $this->builder
            ->shouldReceive('setDatasource')
            ->never()
        ;
        
        $this->type->buildReport($this->builder, $this->options);
    }
    
    public function testBuildReportWithEmptyFilters()
    {
        $this->initializeForBuildTest();
        $this->options['filters'] = null;
        
        $this->builder
            ->shouldReceive('setFilters')
            ->never()
        ;
        
        $this->type->buildReport($this->builder, $this->options);
    }
    
    public function testBuildReportWithEmptyIdGenerator()
    {
        $this->initializeForBuildTest();
        $this->options['id_generator'] = null;
        
        $this->builder
            ->shouldReceive('setIdGenerator')
            ->never()
        ;
        
        $this->type->buildReport($this->builder, $this->options);
    }
    
    public function testSetDefaultOptions()
    {
        $testCase = $this;
        
        $resolver = Mockery::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->shouldReceive('setDefaults')
            ->once()
            ->with(Mockery::on(function($arg) use ($testCase)
            {
                $testCase->assertEquals(array(
                    'datasource' => null, 
                    'filters' => null,
                    'default_renderer' => 'default', 
                    'renderers' => array(),
                    'id_generator' => function (Options $options)
                    {
                        if (!is_null($options['id'])) {
                            
                            return new ConstantValueIdGenerator($options['id']);
                        }
                        
                        return null;
                    },
                    'id' => null
                ), $arg);
                return true;
            }
            ))
            ->andReturn(Mockery::self())
            ->getMock()
            ->shouldReceive('setAllowedTypes')
            ->once()
            ->with(Mockery::on(function($arg) use ($testCase)
            {
                $testCase->assertEquals(array(
                    'datasource' => array(
                        'Yjv\ReportRendering\Datasource\DatasourceInterface', 
                        'null',
                        'array'
                    ),
                    'filters' => array(
                        'Yjv\ReportRendering\Filter\FilterCollectionInterface', 
                        'null'
                    ),
                    'default_renderer' => array('string'), 
                    'renderers' => 'array',
                    'id_generator' => array(
                        'null', 
                        'Yjv\ReportRendering\IdGenerator\IdGeneratorInterface'
                    )
                ), $arg);
                return true;
            }
            ))
            ->andReturn(Mockery::self())
            ->getMock()
            ->shouldReceive('setNormalizers')
            ->once()
            ->with(Mockery::on(function($arg) use ($testCase)
            {
                $testCase->assertEquals(array(
                    'datasource' => function(Options $options, $datasource)
                    {
                        return Factory::normalizeToFactoryArguments($datasource);
                    },
                    'renderers' => function(Options $options, $renderers)
                    {
                        return Factory::normalizeCollectionToFactoryArguments($renderers);
                    },
                    'default_renderer' => function(Options $options, $defaultRenderer)
                    {
                        return (string)$defaultRenderer;
                    }
                ), $arg);
                return true;
            }
            ))
            ->andReturn(Mockery::self())
            ->getMock()
        ;
        $this->type->setDefaultOptions($resolver);
    }
    
    public function testResolvingOfIdGenerator()
    {
        $resolver = new OptionsResolver();
        $this->type->setDefaultOptions($resolver);
        $options = $resolver->resolve();
        $this->assertNull($options['id_generator']);
        $options = $resolver->resolve(array('id' => 43));
        $this->assertInstanceOf('Yjv\ReportRendering\IdGenerator\ConstantValueIdGenerator', $options['id_generator']);
        $this->assertEquals(43, $options['id_generator']->getId(Mockery::mock('Yjv\ReportRendering\Report\ReportInterface')));
    }
    
    public function testResolvingOfRenderers()
    {
        $resolver = new OptionsResolver();
        $this->type->setDefaultOptions($resolver);
        $renderers = array(
                
                'renderer1' => Mockery::mock('Yjv\ReportRendering\Renderer\RendererInterface'),
                'default1' => array(Mockery::mock('Yjv\ReportRendering\Renderer\RendererInterface')),
                'default2' => array(Mockery::mock('Yjv\ReportRendering\Renderer\RendererInterface'), array('key' => 'value'))
        );
        $options = $resolver->resolve(array('renderers' => $renderers));
        $this->assertEquals(array(
                
                'renderer1' => array($renderers['renderer1'], array()),
                'default1' => array($renderers['default1'][0], array()),
                'default2' => $renderers['default2']
        ), $options['renderers']);
    }
    
    public function testGetParent()
    {
        $this->assertFalse($this->type->getParent());
    }
    
    public function testGetName()
    {
        $this->assertEquals('report', $this->type->getName());
    }
    
    public function testGetBuilder()
    {
        $options = array('key' => 'value');
        
        $this->assertEquals(new ReportBuilder($this->factory, new EventDispatcher(), $options), $this->type->createBuilder($this->factory, $options));
    }
    
    protected function getFullOptions()
    {
        return array(
        
            'datasource' => array(Mockery::mock('Yjv\ReportRendering\Datasource\DatasourceInterface'), array('key' => 'value')),
            'default_renderer' => 'default1',
            'filters' => Mockery::mock('Yjv\ReportRendering\Filter\FilterCollectionInterface'),
            'renderers' => array(

                'renderer1' => array(Mockery::mock('Yjv\ReportRendering\Renderer\RendererInterface'), array()),
                'default1' => array(Mockery::mock('Yjv\ReportRendering\Renderer\RendererInterface'), array())
            ),
            'id_generator' => Mockery::mock('Yjv\ReportRendering\IdGenerator\IdGeneratorInterface')
        );
    }
    
    protected function initializeForBuildTest()
    {
        $this->options = $this->getFullOptions();
        $this->builder = Mockery::mock('Yjv\ReportRendering\Report\ReportBuilderInterface')
            ->shouldReceive('setDatasource')
            ->once()
            ->with(
                $this->options['datasource'][0],
                $this->options['datasource'][1]
            )
            ->byDefault()
            ->getMock()
            ->shouldReceive('setDefaultRenderer')
            ->once()
            ->with($this->options['default_renderer'])
            ->byDefault()
            ->getMock()
            ->shouldReceive('setFilters')
            ->once()
            ->with($this->options['filters'])
            ->byDefault()
            ->getMock()
            ->shouldReceive('addRenderer')
            ->once()
            ->with('renderer1', $this->options['renderers']['renderer1'][0], $this->options['renderers']['renderer1'][1])
            ->byDefault()
            ->getMock()
            ->shouldReceive('addRenderer')
            ->once()
            ->with('default1', $this->options['renderers']['default1'][0], $this->options['renderers']['default1'][1])
            ->byDefault()
            ->getMock()
            ->shouldReceive('setIdGenerator')
            ->once()
            ->with($this->options['id_generator'])
            ->byDefault()
            ->getMock()
        ;
    }
}
