<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

use Mongolog\Bundle\MongologBrowserBundle\Model\Log;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class LogRepository
{
    /**
     * @var \MongoDB $conn
     */
    private $conn;

    /**
     * @param \MongoClient $conn
     * @param $databaseName
     * @param $collectionName
     */
    public function __construct(\MongoClient $conn, $databaseName, $collectionName)
    {
        $this->conn = $conn->selectDB($databaseName)->createCollection($collectionName);
    }

    /**
     * @return \MongoCollection
     */
    private function createQueryBuilder()
    {
        return $this->conn;
    }

    /**
     * Initialize a QueryBuilder of latest log entries.
     *
     * @return array
     */
    private function getLogsQueryBuilder($page, $logsPerPage, $search = array())
    {
        $skip = $logsPerPage * ($page - 1);

        $data = $this->createQueryBuilder()->find($search);

        $data->skip($skip)->limit($logsPerPage)->sort(array('$natural' => -1));

        return array(
            'total' => $data->count(),
            'results' => iterator_to_array($data),
        );
    }

    public function all($page, $logsPerPage)
    {
        return $this->getLogsQueryBuilder($page, $logsPerPage);
    }

    public function search($page, $logsPerPage, $data)
    {
        $query = array();

        if (null !== $data['term'])
        {
            $query['message'] = array(
                '$regex' => $data['term']
            );
        }

        if (null !== $data['level'])
        {
            $query['level'] = array(
                '$eq' => $data['level']
            );
        }

        if ($data['date_from'] instanceof \DateTime)
        {
            $query['datetime']['$gte'] = date_format($data['date_from'], 'Y-m-d H:i:s');
        }

        if ($data['date_to'] instanceof \DateTime)
        {
            $query['datetime']['$lte'] = date_format($data['date_to'], 'Y-m-d H:i:s');
        }

        return $this->getLogsQueryBuilder($page, $logsPerPage, $query);
    }

    /**
     * Retrieve a log entry by his ID.
     *
     * @param integer $id
     *
     * @return Log|null
     */
    public function getLogById($id)
    {
        $log = $this->createQueryBuilder()
            ->findOne(
                array(
                    '_id' => array(
                        '$eq' => new \MongoId($id),
                    )
                )
            );

        if(!$log) return;

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
        return array(
            100 => 'Debug',
            200 => 'Info',
            250 => 'Notice',
            300 => 'Warning',
            400 => 'Error',
            500 => 'Critical',
            550 => 'Alert',
            600 => 'Emergency'
        );
    }
}
