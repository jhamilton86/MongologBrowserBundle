<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class Log
{
    protected $id;
    protected $channel;
    protected $level;
    protected $levelName;
    protected $message;
    protected $date;
    protected $context;
    protected $extra;
    protected $serverData;
    protected $postData;
    protected $getData;

    public function __construct(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException();
        }

        $this->id         = $data['id'];
        $this->channel    = $data['channel'];
        $this->level      = $data['level'];
        $this->levelName  = $data['level_name'];
        $this->message    = $data['message'];
        $this->date       = new \DateTime($data['datetime']);
        $this->context    = isset($data['context'])     ? $data['context']    : array();
        $this->extra      = isset($data['extra'])       ? $data['extra']      : array();
//        $this->serverData = isset($data['http_server']) ? $data['http_server']: array();
//        $this->postData   = isset($data['http_post'])   ? $data['http_post']  : array();
//        $this->getData    = isset($data['http_get'])    ? $data['http_get']   : array();

    }

    public function __toString()
    {
        return mb_strlen($this->message) > 100 ? sprintf('%s...', mb_substr($this->message, 0, 100)) : $this->message;
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

    public function getServerData()
    {
        return $this->serverData;
    }

    public function getPostData()
    {
        return $this->postData;
    }

    public function getGetData()
    {
        return $this->getData;
    }
}
