<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

/**
 * Class DateRangeSet
 * @package Mongolog\Bundle\MongologBrowserBundle\Model
 */
class DateRangeSet
{
    private $start;

    private $end;

    public function __construct(\DateTime $start, \DateTime $end)
    {
        if ($start > $end)
        {
            throw new \InvalidArgumentException('Start date cannot be after the end date.');
        }

        $this->start = $start;

        $this->end = $end;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }
}
