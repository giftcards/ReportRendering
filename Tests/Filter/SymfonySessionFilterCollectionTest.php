<?php
namespace Yjv\ReportRendering\Tests\Filter;

use Yjv\ReportRendering\Filter\SymfonySessionFilterCollection;

use Yjv\ReportRendering\Filter\ArrayFilterCollection;

class SymfonySessionFilterCollectionTest extends ArrayFilterCollectionTest
{
	protected $session;
	public $sessionData;
	public $currentReportId;
	
	public function setUp() {
		
		$this->session = $this->getMockBuilder('Symfony\\Component\\HttpFoundation\\Session\\SessionInterface')->getMock();
		$this->filters = new SymfonySessionFilterCollection($this->session);
	}
	
	public function testGettersSetters(){
		
		$this->setUpSessionExpects(12, 8);

		$this->currentReportId = 'special_report';
		$this->filters->setReportId($this->currentReportId);
		parent::testGettersSetters();

		$this->currentReportId = 'other_special_report';
		$this->filters->setReportId($this->currentReportId);
		parent::testGettersSetters();
	}
	
	protected function setUpSessionExpects($getCount, $setCount){
		
		$tester = $this;
		
		$this->session
			->expects($this->exactly($getCount))
			->method('get')
			->will($this->returnCallback(function($path, $default) use ($tester){
				
				$tester->assertContains($tester->currentReportId, $path);
				return isset($tester->sessionData[$path]) ? $tester->sessionData[$path] : $default;
			}))
		;
		
		$this->session
			->expects($this->exactly($setCount))
			->method('set')
			->will($this->returnCallback(function($path, $value) use ($tester){
				
				$tester->assertContains($tester->currentReportId, $path);
				$tester->sessionData[$path] = $value;
			}))
		;
	}
}
