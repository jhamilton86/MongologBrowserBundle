<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

/**
 * @author Jonathan Hamilton
 */
class DateRangeSetTest extends \PHPUnit_Framework_TestCase
{
    public function testItInitializesLogRepositoryObject()
    {
        $start = new \DateTime('2015-08-31 00:00:00');
        $end = new \DateTime('2015-09-01 00:00:00');

        $dateRange = new DateRangeSet($start, $end);

        $this->assertEquals($start, $dateRange->getStart());
        $this->assertEquals($end, $dateRange->getEnd());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Start date cannot be after the end date.
     */
    public function testItThrowsInvalidArgumentExceptionForInvalidDate()
    {
        $start = new \DateTime('2015-09-01 00:00:00');
        $end = new \DateTime('2015-08-31 00:00:00');

        new DateRangeSet($start, $end);
    }


}