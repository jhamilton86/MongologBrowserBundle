<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class Log
{
    const MAX_MESSAGE_LENGTH = 100;

    private $requiredParams = array(
        'id',
        'channel',
        'level',
        'level_name',
        'message',
        'datetime'
    );

    private $id;
    private $channel;
    private $level;
    private $levelName;
    private $message;
    private $date;
    private $context;
    private $extra;

    public function __construct(array $data)
    {
        foreach($this->requiredParams as $param)
        {
            if (!isset($data[$param])) {
                throw new \InvalidArgumentException("Property '$param' must be present in input array");
            }
        }

        $date = new \DateTime();
        $date->setTimestamp($data['datetime']);

        $this->id         = $data['id'];
        $this->channel    = $data['channel'];
        $this->level      = $data['level'];
        $this->levelName  = $data['level_name'];
        $this->message    = $data['message'];
        $this->date       = $date;
        $this->context    = isset($data['context'])     ? $data['context']    : array();
        $this->extra      = isset($data['extra'])       ? $data['extra']      : array();

    }

    public function __toString()
    {
        return $this->getTruncatedMessage();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getLevelName()
    {
        return $this->levelName;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getTruncatedMessage()
    {
        return mb_strlen($this->message) > self::MAX_MESSAGE_LENGTH ? sprintf('%s...', mb_substr($this->message, 0, self::MAX_MESSAGE_LENGTH)) : $this->message;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function getSearchExtra()
    {
        return $this->buildSearch("extra.", $this->extra);
    }

    public function getSearchContext()
    {
        return $this->buildSearch("context.", $this->context);
    }

    private function buildSearch($prefix, array $items)
    {
        $parts = array();

        foreach($items as $name => $item)
        {
            $parts[] = "{$prefix}{$name} = '{$item}'";
        }

        return implode(',', $parts);
    }
}
