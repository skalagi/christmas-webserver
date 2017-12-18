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
     * @var LogDisplayer
     */
    private $displayer;

    /**
     * Database constructor.
     * @param LogDisplayer $displayer
     * @param $pathToDatabase
     */
    public function __construct(LogDisplayer $displayer, $pathToDatabase)
    {
        $this->connection = new \PDO('sqlite:'.__DIR__.DIRECTORY_SEPARATOR.$pathToDatabase);
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->displayer = $displayer;
    }

    /**
     * @param LogEntity $log
     * @return \PDOStatement
     */
    public function addLog(LogEntity $log)
    {
        $this->displayer->renderLog($log);

        return $this->connection->query(sprintf('INSERT INTO `syslog`(`name`, `created_time`, `initiator`, `data`)
           VALUES("%s", "%s", "%s", \'%s\')
        ',
            $log->name,
            $log->createdTime->format('Y-m-d H:i:s'),
            $log->initiator,
            json_encode($log->data)

        ));
    }

    /**
     * @param array $criteria
     * @return array|LogEntity[]
     */
    public function getLogs(array $criteria = [])
    {
        $sqlWhere = '';
        if(isset($criteria['name'])) $sqlWhere .= ' AND `name` = \''.$criteria['name'].'\'';
        if(isset($criteria['created_time'])) $sqlWhere .= ' AND `created_time` LIKE \''.$criteria['created_time'].'\'';
        $sqlWhere = ' WHERE '.ltrim($sqlWhere, ' AND');

        $logs = [];
        foreach($this->connection->query('SELECT * FROM syslog t'.$sqlWhere) as $row) {
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
            $log->data = (array)json_decode($row['data'], JSON_OBJECT_AS_ARRAY);
            $logs[] = $log;
        }
        return $logs;
    }
}