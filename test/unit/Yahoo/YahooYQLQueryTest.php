<?php

require_once 'PHPUnit/Framework.php';

require dirname(__FILE__).'/../../../lib/Yahoo/YahooYQLQuery.class.php';

class YahooYQLQueryTest extends PHPUnit_Framework_TestCase {


  public function setup()
  {
    $this->yql = new YahooYQLQuery();
  }

	public function testQueryValid() {
	  /*
	    Tests the calling of yql public api given a valid query
	  */
	  $response = $this->yql->execute('select * from delicious.feeds.popular');
	  $this->assertTrue(isset($response->query) && isset($response->query->results), 'Response contains valid results');
	}

	public function testQueryInvalid() {
	  /*
	    Tests the calling of yql public api given a invalid query
	  */
	  $response = $this->yql->execute('select * from delicious.feeds.unknown_test');

	  $this->assertTrue(isset($response->error), 'Response contains error');
	  $this->assertEquals('No definition found for Table delicious.feeds.unknown_test', $response->error->description, 'Response contains valid error description');
	}

  public function tearDown()
  {
    unset($this->yql);
  }
}
