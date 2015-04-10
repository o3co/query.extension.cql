<?php
namespace O3Co\Query\Extension\CQL\Tests;

use O3Co\Query\Query\SimpleQueryBuilder;
use O3Co\Query\Extension\CQL\CQLPersister;
use O3Co\Query\Bridge\GuzzleHttp\ProxyClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Mock as GuzzleMock;
use GuzzleHttp\Message\Response as GuzzleResponse;

class CQLPersisterTest extends \PHPUnit_Framework_TestCase 
{
    public function testBasic()
    {
        $guzzleClient = new GuzzleClient();
        $guzzleClient->getEmitter()->attach(new GuzzleMock(array(new GuzzleResponse(200, array('Content-Type' => 'application/json', 'body' => '[]')))));
        $client = new ProxyClient($guzzleClient);
        $persister = new CQLPersister($client);

        $query = $this->getQuery();

        $query = $persister->getNativeQuery($query);

        $this->assertEquals('q=' . urlencode('foo:=:Foo'), $query);
    }

    protected function getQuery()
    {
        $qb = new SimpleQueryBuilder();
        $qb->addWhere($qb->expr()->eq('foo', 'Foo'));

        return $qb->getQuery();
    }
}

