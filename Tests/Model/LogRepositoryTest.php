<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

/**
 * @author Jonathan Hamilton
 */
class LogRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testItInitializesLogRepositoryObject()
    {
        $mongo = $this->getMockBuilder('MongoClient')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoDb = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCollection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $mongo->expects($this->once())->method('selectDB')->with('foo')->willReturn($mongoDb);
        $mongoDb->expects($this->once())->method('createCollection')->with('bar')->willReturn($mongoCollection);


        $mongoCursor = $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCollection->expects($this->once())->method('find')->with(array())->willReturn($mongoCursor);
        $mongoCursor->expects($this->once())->method('skip')->with(20)->willReturnSelf();
        $mongoCursor->expects($this->once())->method('limit')->with(10)->willReturnSelf();
        $mongoCursor->expects($this->once())->method('sort')->with(array('datetime' => -1))->willReturnSelf();
        $mongoCursor->expects($this->once())->method('count')->willReturn(5);

        $repository = new LogRepository($mongo, 'foo', 'bar');

        $results = $repository->all(3, 10);

        $expected = array(
            'total' => 5,
            'results' => array(),
        );

        $this->assertEquals($expected, $results);
    }

    public function testTheSearchMethodReturnsArray()
    {
        $mongo = $this->getMockBuilder('MongoClient')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoDb = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCollection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $mongo->expects($this->once())->method('selectDB')->with('foo')->willReturn($mongoDb);
        $mongoDb->expects($this->once())->method('createCollection')->with('bar')->willReturn($mongoCollection);


        $mongoCursor = $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();


        $startDate = new \DateTime('2015-09-01 00:00:00');
        $endDate = new \DateTime('2015-09-02 00:00:00');

        $mongoCollection->expects($this->once())->method('find')
            ->with(array(
                'message' => array(
                    '$regex' => 'foo'
                ),
                'level' => array(
                    '$eq' => 100
                ),
                'datetime' => array(
                    '$gte' => $startDate->getTimestamp(),
                    '$lte' => $endDate->getTimestamp()
                )
            ))
            ->willReturn($mongoCursor);


        $mongoCursor->expects($this->once())->method('skip')->with(20)->willReturnSelf();
        $mongoCursor->expects($this->once())->method('limit')->with(10)->willReturnSelf();
        $mongoCursor->expects($this->once())->method('sort')->with(array('datetime' => -1))->willReturnSelf();
        $mongoCursor->expects($this->once())->method('count')->willReturn(5);

        $repository = new LogRepository($mongo, 'foo', 'bar');

        $search = array(
            'message' => array(
                '$regex' => 'foo'
            ),
        );

        $results = $repository->search(3, 10, new DateRangeSet($startDate, $endDate), $search, 100);

        $expected = array(
            'total' => 5,
            'results' => array()
        );

        $this->assertEquals($expected, $results);
    }

    public function testItCanGetLogById()
    {
        $mongo = $this->getMockBuilder('MongoClient')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoDb = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCollection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $mongo->expects($this->once())->method('selectDB')->with('foo')->willReturn($mongoDb);
        $mongoDb->expects($this->once())->method('createCollection')->with('bar')->willReturn($mongoCollection);

        $mongoCollection->expects($this->once())->method('findOne')->willReturn(array(
            '_id' => '507f1f77bcf86cd799439011',
            'channel' => '',
            'level' => 100,
            'level_name' => 'Debug',
            'message' => 'Foo bar',
            'datetime' => '2015-09-01 00:00:00'
        ));

        $repository = new LogRepository($mongo, 'foo', 'bar');

        $this->assertNotNull($repository->getLogById('507f1f77bcf86cd799439011'));
    }
}