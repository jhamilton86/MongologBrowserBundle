<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

/**
 * @author Jonathan Hamilton
 */
class LogModelTest extends \PHPUnit_Framework_TestCase
{

    private function generateMessageString($length)
    {
        return str_repeat('a', $length);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Property 'id' must be present in input array
     */
    public function testItThrowsInvalidArgumentExceptionForMissingID()
    {
        new Log([]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed to parse time string
     */
    public function testItThrowsInvalidArgumentExceptionForInvalidDate()
    {
        $data = array(
            'id' => 12345,
            'channel' => 'foobar',
            'level' => 500,
            'level_name' => 'Warning',
            'message' => 'fizz buzz',
            'datetime' => 'invalid date'
        );

        new Log($data);
    }

    public function testItInitializesALogObject()
    {
        $data = array(
            'id' => 12345,
            'channel' => 'foobar',
            'level' => 500,
            'level_name' => 'Warning',
            'message' => 'fizz buzz',
            'datetime' => '2015-09-01 02:03:04',
            'context' => array('foo' => 1, 'bar' => '2'),
            'extra' => array()
        );

        $log = new Log($data);

        $this->assertEquals($data['id'], $log->getId());
        $this->assertEquals($data['channel'], $log->getChannel());
        $this->assertEquals($data['level'], $log->getLevel());
        $this->assertEquals($data['level_name'], $log->getLevelName());
        $this->assertEquals($data['message'], $log->getMessage());
        $this->assertEquals(new \DateTime($data['datetime']), $log->getDate());
        $this->assertEquals($data['context'], $log->getContext());
        $this->assertEquals($data['extra'], $log->getExtra());
    }

    public function testThatMessagesAreEqualUnderMaxMessageLength()
    {
        $data = array(
            'id' => 12345,
            'channel' => 'foobar',
            'level' => 500,
            'level_name' => 'Warning',
            'message' =>  $this->generateMessageString(Log::MAX_MESSAGE_LENGTH - 10),
            'datetime' => '2015-09-01 02:03:04'
        );

        $log = new Log($data);

        $this->assertEquals($data['message'], (string)$log);
    }

    public function testItAddsEllipsisToMessagesOverMaxLength()
    {
        $data = array(
            'id' => 12345,
            'channel' => 'foobar',
            'level' => 500,
            'level_name' => 'Warning',
            'message' =>  $this->generateMessageString(Log::MAX_MESSAGE_LENGTH + 10),
            'datetime' => '2015-09-01 02:03:04'
        );

        $log = new Log($data);

        $this->assertStringEndsWith('...', (string)$log);
    }

}
