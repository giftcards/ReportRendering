<?php
namespace Yjv\ReportRendering\Tests\Renderer\Extension\Core;

use Yjv\ReportRendering\Renderer\Html\HtmlRenderer;

use Yjv\ReportRendering\Util\Factory;

use Yjv\ReportRendering\Renderer\Extension\Core\Type\HtmlType;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Yjv\ReportRendering\Tests\Renderer\Extension\Core\Type\TypeTestCase;

use Mockery;

class HtmlTypeTest extends TypeTestCase
{
    protected $formFactory;
    protected $widgetRenderer;
    
    public function setUp()
    {
        parent::setUp();
        $this->widgetRenderer = Mockery::mock('Yjv\ReportRendering\Widget\WidgetRendererInterface');
        $this->formFactory = Mockery::mock('Symfony\Component\Form\FormFactoryInterface');
        $this->type = new HtmlType($this->widgetRenderer, $this->formFactory);
    }
    
    public function testSetDefaultOptions()
    {
        $testCase = $this;
        $type = $this->type;
        
        $resolver = Mockery::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->shouldReceive('setDefaults')
            ->once()
            ->with(Mockery::on(function($value) use ($testCase, $type) 
            {
                $testCase->assertEquals(array(
        
                    'template' => null,
                    'filter_form' => function(Options $options) use ($type) {
                        
                        return $type->buildFilterForm($options);
                    },
                    'widget_attributes' => array(),
                    'constructor' => array($type, 'rendererConstructor'),
                    'filter_fields' => array(),
                    'filter_form_options' => array('csrf_protection' => false)
                ), $value);
                return true;
            }))
            ->andReturn(Mockery::self())
            ->getMock()
            ->shouldReceive('setAllowedTypes')
            ->once()
            ->with(array(
                'filter_form' => array(
                    'null', 
                    'Symfony\Component\Form\FormInterface'
                ),
                'widget_attributes' => 'array',
                'template' => 'string',
                'filter_fields' => 'array'
            ))
            ->andReturn(Mockery::self())
            ->getMock()
            ->shouldReceive('setNormalizers')
            ->once()
            ->with(Mockery::on(function($value) use ($testCase) 
            {
                $testCase->assertEquals(array(
                    'filter_fields' => function(Options $options, $filterFields)
                    {
                        return Factory::normalizeOptionsCollectionToFactoryArguments($options, $filterFields);
                    }
                ), $value);
                return true;
            }))
            ->andReturn(Mockery::self())
            ->getMock()
        ;
        $this->type->setDefaultOptions($resolver);
    }
    
    public function testRendererConstructor()
    {
        $grid = Mockery::mock('Yjv\ReportRendering\Renderer\Grid\GridInterface');
        $form = Mockery::mock('SYmfony\Component\Form\FormInterface');
        $attributes = array('key' => 'value');
        $expectedRenderer = new HtmlRenderer($this->widgetRenderer, $grid, 'template');
        $expectedRenderer->setAttribute('key', 'value');
        $expectedRenderer->setFilterForm($form);
        $builder = Mockery::mock('Yjv\ReportRendering\Renderer\RendererBuilderInterface')
            ->shouldReceive('getGrid')
            ->once()
            ->andReturn($grid)
            ->getMock()
            ->shouldReceive('getOption')
            ->once()
            ->with('template')
            ->andReturn('template')
            ->getMock()
            ->shouldReceive('getOption')
            ->twice()
            ->with('filter_form')
            ->andReturn($form)
            ->getMock()
            ->shouldReceive('getOption')
            ->once()
            ->with('widget_attributes')
            ->andReturn($attributes)
            ->getMock()
        ;
        $this->assertEquals($expectedRenderer, $this->type->rendererConstructor($builder));
    }
    
    public function testBuildFilterForm()
    {
        $fields = array(
            'field1' => array('sfsdfds', array('key1' => 'value1')),
            'field2' => array('sfsdfds', array('key2' => 'value2')),
        );
        $formOptions = array('key' => 'value');
        
        $options = Mockery::mock('Symfony\Component\OptionsResolver\Options')
            ->shouldReceive('offsetGet')
            ->once()
            ->with('filter_form_options')
            ->andReturn($formOptions)
            ->getMock()
            ->shouldReceive('offsetGet')
            ->once()
            ->with('filter_fields')
            ->andReturn($fields)
            ->getMock()
        ;
        $form = Mockery::mock('Symfony\Component\Form\FormInterface');
        $builder = Mockery::mock('Symfony\Component\Form\FormBuilderInterface')
            ->shouldReceive('add')
            ->once()
            ->with('field1', $fields['field1'][0], $fields['field1'][1])
            ->getMock()
            ->shouldReceive('add')
            ->once()
            ->with('field2', $fields['field2'][0], $fields['field2'][1])
            ->getMock()
            ->shouldReceive('getForm')
            ->once()
            ->andReturn($form)
            ->getMock()
        ;
        $this->formFactory
            ->shouldReceive('createBuilder')
            ->once()
            ->with('form', null, $formOptions)
            ->andReturn($builder)
        ;
        $this->assertSame($form, $this->type->buildFilterForm($options));
    }
    
    public function testBuildFilterFormWithNoFormFactory()
    {
        $type = new HtmlType($this->widgetRenderer);
        $this->assertNull($type->buildFilterForm(Mockery::mock('Symfony\Component\OptionsResolver\Options')));
    }
        
    public function testGetName()
    {
        $this->assertEquals('html', $this->type->getName());
    }
    
    public function testGetParent()
    {
        $this->assertEquals('gridded', $this->type->getParent());
    }
}