<?php

namespace Syntax\Service;

use Syntax\Model\Application\LogEntity;

class Database
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * Database constructor.
     * @param $pathToDatabase
     */
    public function __construct($pathToDatabase)
    {
        $this->connection = new \PDO('sqlite:'.$pathToDatabase);
    }

    /**
     * @param LogEntity $log
     * @return \PDOStatement
     */
    public function addLog(LogEntity $log)
    {
        return $this->connection->query(sprintf('INSERT INTO `syslog` SET
          `name` = "%s",
          `created_time` = "%s",
          `initiator` = "%s",
          `data` = "%s"
        ', $log->name, $log->createdTime->format('Y-m-d H:i:s'), $log->initiator, json_encode($log->data)));
    }

    /**
     * @param array $criteria
     * @return array|LogEntity[]
     */
    public function getLogs(array $criteria = [])
    {
        $sqlWhere = '';
        if(isset($criteria['name'])) $sqlWhere .= ' WHERE `name` = "'.$criteria['name'].'"';
        if(isset($criteria['created_time'])) $sqlWhere .= ' WHERE `created_time` = "'.$criteria['created_time'].'"';

        $logs = [];
        foreach($this->connection->query('SELECT * FROM `syslog`'.$sqlWhere) as $row) {
            $log = new LogEntity();
            $log->name = $row['name'];
            $log->createdTime = new \DateTime($row['created_time']);
            $log->initiator = $row['initiator'];
            $log->data = json_decode($row['data']);
            $logs[] = $log;
        }

        return $logs;
    }

    /**
     * @param $wherePart
     * @return array|LogEntity[]
     */
    public function selectQuery($wherePart)
    {
        $logs = [];
        foreach($this->connection->query('SELECT * FROM `syslog` '.$wherePart) as $row) {
            $log = new LogEntity();
            $log->name = $row['name'];
            $log->createdTime = new \DateTime($row['created_time']);
            $log->initiator = $row['initiator'];
            $log->data = json_decode($row['data']);
            $logs[] = $log;
        }
        return $logs;
    }
}