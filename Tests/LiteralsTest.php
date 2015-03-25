<?php
namespace O3Co\Query\Extension\CQL\Tests;

use O3Co\Query\Extension\CQL\Literals;

class LiteralsTest extends \PHPUnit_Framework_TestCase 
{
	public function testSuccess()
	{
		$literals = new Literals();

		$this->assertEquals('\\"hello\\"', $literals->escape('"hello"'));

	}
}

