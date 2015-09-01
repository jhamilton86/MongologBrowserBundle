<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Model;

use Mongolog\Bundle\MongologBrowserBundle\Model\Log;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;

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

        if (!isset($data['datefrom']) && !isset($data['dateto']))
        {
            throw new \Exception('A valid date range must be entered');
        }

        $datefrom = new \DateTime($data['datefrom']);
        $dateto = new \DateTime($data['dateto']);

        if ($datefrom > $dateto)
        {
            throw new \Exception('Start date cannot be after the end date.');
        }

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

        if ($datefrom !== null)
        {
            $query['datetime']['$gte'] = date_format($datefrom, 'Y-m-d H:i:s');
        }

        if ($dateto !== null)
        {
            $query['datetime']['$lte'] = date_format($dateto, 'Y-m-d H:i:s');
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
