<?php
namespace Yjv\Bundle\ReportRenderingBundle\DataTransformer;

use Yjv\Bundle\ReportRenderingBundle\DataTransformer\DataTransformerNotFoundException;

class DataTransformerRegistry {

	protected $dataTransformers = array();
	
	public function add($name, DataTransformerInterface $dataTransformer) {
		
		$this->dataTransformers[$name] = $dataTransformer;
		return $this;
	}
	
	public function get($name) {
		
		if (!isset($this->dataTransformers[$name])) {
			
			throw new DataTransformerNotFoundException($name);
		}
		
		return clone $this->dataTransformers[$name];
	}
}
