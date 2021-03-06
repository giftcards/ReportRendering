<?php
namespace Yjv\ReportRendering\Renderer\Grid\Column;

use Yjv\ReportRendering\DataTransformer\DataTransformerRegistry;

use Yjv\TypeFactory\AbstractTypeFactoryBuilder;

class ColumnFactoryBuilder extends AbstractTypeFactoryBuilder
{
    protected $dataTransformerRegistry;
    
    public function setDataTransformerRegistry(DataTransformerRegistry $dataTransformerRegistry)
    {
        $this->dataTransformerRegistry = $dataTransformerRegistry;
        return $this;
    }
    
    public function getDataTransformerRegistry()
    {
        if (!$this->dataTransformerRegistry) {
            
            $this->dataTransformerRegistry = $this->getDefaultDataTransformerRegistry();
        }
        
        return $this->dataTransformerRegistry;
    }
    
    protected function getFactoryInstance()
    {
        return new ColumnFactory($this->getTypeResolver(), $this->getDataTransformerRegistry());
    }
    
    protected function getDefaultDataTransformerRegistry()
    {
        return new DataTransformerRegistry();
    }
}
