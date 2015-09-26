<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class LogRepository
{

    private static $logLevelValues = array(
        100 => 'Debug',
        200 => 'Info',
        250 => 'Notice',
        300 => 'Warning',
        400 => 'Error',
        500 => 'Critical',
        550 => 'Alert',
        600 => 'Emergency'
    );

    /**
     * @var \MongoCollection $conn
     */
    private $collection;

    /**
     * @param \MongoCollection $collection
     */
    public function __construct(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Initialize a QueryBuilder of latest log entries.
     *
     * @param $page
     * @param $logsPerPage
     * @param array $search
     * @return array
     */
    private function getLogsQueryBuilder($page, $logsPerPage, $search = array())
    {
        $skip = $logsPerPage * ($page - 1);

        $cursor = $this->collection->find($search);

        $cursor->skip($skip)->limit($logsPerPage)->sort(array('_id' => -1));

        return array(
            'total' => $cursor->count(),
            'results' => array_map([$this, 'convertArrayToEntity'], iterator_to_array($cursor)),
        );
    }

    private function convertArrayToEntity(array $data)
    {
        $data['id'] = $data['_id'];

        return new Log($data);
    }

    /**
     * @param $page
     * @param $logsPerPage
     * @return array
     */
    public function all($page, $logsPerPage)
    {
        return $this->getLogsQueryBuilder($page, $logsPerPage);
    }

    /**
     * @param $page
     * @param $logsPerPage
     * @param DateRangeSet $dateRange
     * @param array $query
     * @param null $level
     * @return array
     */
    public function search($page, $logsPerPage, DateRangeSet $dateRange, array $query = array(), $level = null)
    {
        if ($level > 0 && in_array($level, array_keys(self::getLogsLevel())))
        {
            $query['level'] = array(
                '$eq' => $level
            );
        }

        $query['datetime'] = array(
            '$gte' => $dateRange->getStart()->getTimestamp(),
            '$lte' => $dateRange->getEnd()->getTimestamp(),
        );

        return $this->getLogsQueryBuilder($page, $logsPerPage, $query);
    }

    /**
     * Retrieve a log entry by his ID.
     *
     * @param string $id
     *
     * @return Log|null
     */
    public function getLogById($id)
    {
        $log = $this->collection
            ->findOne(
                array(
                    '_id' => array(
                        '$eq' => new \MongoId($id),
                    )
                )
            );

        if(!$log) return null;

        return $this->convertArrayToEntity($log);
    }

    /**
     * Returns a array of levels with count entries used by logs.
     *
     * @return array
     */
    public function getLogsLevel()
    {
        return self::$logLevelValues;
    }
}
