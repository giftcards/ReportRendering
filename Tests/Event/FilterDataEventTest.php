<?php
namespace Yjv\ReportRendering\Test\Event;
use Yjv\ReportRendering\ReportData\ReportData;

use Yjv\ReportRendering\Event\FilterDataEvent;


class FilterDataEventTest extends DataEventTest{

	protected $data;
	
	public function setUp() {
	
		parent::setUp();
		$this->data = new ReportData(array(), 10);
		$this->dataEvent = new FilterDataEvent($this->rendererName, $this->renderer, $this->datasource, $this->filters, $this->data);
	}
	
	public function testGettersSetters(){
		
		parent::testGettersSetters();
		$this->assertEquals($this->data, $this->dataEvent->getData());
		
		$oldData = $this->dataEvent->getData();
		$newData = new ReportData(array(), 32);
		
		$this->dataEvent->setData($newData);
		$this->assertEquals($newData, $this->dataEvent->getData());
		$this->assertNotEquals($oldData, $this->dataEvent->getData());
	}
}
