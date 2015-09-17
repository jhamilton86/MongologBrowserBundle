<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;
use MongoClient;

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
     * @param MongoClient $conn
     * @param $databaseName
     * @param $collectionName
     */
    public function __construct(MongoClient $conn, $databaseName, $collectionName)
    {
        $this->collection = $conn->selectDB($databaseName)->createCollection($collectionName);
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

        $data = $this->collection->find($search);

        $data->skip($skip)->limit($logsPerPage)->sort(array('$natural' => -1));

        return array(
            'total' => $data->count(),
            'results' => iterator_to_array($data),
        );
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
     * @param null $message
     * @param null $level
     * @return array
     */
    public function search($page, $logsPerPage, DateRangeSet $dateRange, $message = null, $level = null)
    {
        $query = array();

        if (null !== $message && $message !== '')
        {
            $query['message'] = array(
                '$regex' => $message
            );
        }

        if ($level > 0 && in_array($level, array_keys(self::getLogsLevel())))
        {
            $query['level'] = array(
                '$eq' => $level
            );
        }

        $query['datetime']['$gte'] = $dateRange->getStart();

        $query['datetime']['$lte'] = $dateRange->getEnd();

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

        $log['id'] = $log['_id'];

        return new Log($log);
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
