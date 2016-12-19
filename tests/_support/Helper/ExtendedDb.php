<?php

namespace Helper;

class ExtendedDb extends \Codeception\Module\Db
{
    /**
     * @param string $table
     * @param array $criteria
     *
     * @return array
     */
    public function grabRowFromDatabase($table, $criteria = [])
    {
        $query = $this->driver->select('*', $table, $criteria);

        $parameters = array_values($criteria);

        $this->debugSection('Query', $query);

        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }

        $sth = $this->driver->executeQuery($query, $parameters);

        return $sth->fetch();
    }
}