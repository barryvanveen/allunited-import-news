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

    public function grabRowsFromDatabase($table, $criteria = [])
    {
        $query = $this->select('*', $table, $criteria);

        $parameters = array_values($criteria);

        $this->debugSection('Query', $query);

        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }

        $sth = $this->driver->executeQuery($query, $parameters);

        return $sth->fetchAll();
    }

    public function select($column, $table, array &$criteria)
    {
        $where = $this->generateWhereClause($criteria);

        $query = "SELECT %s FROM %s %s";

        return sprintf($query, $column, $table, $where);
    }

    protected function generateWhereClause(array &$criteria)
    {
        if (empty($criteria)) {
            return '';
        }

        $params = [];
        foreach ($criteria as $k => $v) {
            if ($v === null) {
                $params[] = $k . " IS NULL ";
                unset($criteria[$k]);
            } elseif (strpos(strtolower($k), ' like') > 0) {
                $k = str_replace(' like', '', strtolower($k));
                $params[] = $k . " LIKE ? ";
            } elseif (strpos(strtolower($k), ' >=') > 0) {
                $k = str_replace(' >=', '', strtolower($k));
                $params[] = $k . " >= ? ";
            } elseif (strpos(strtolower($k), ' <=') > 0) {
                $k = str_replace(' <=', '', strtolower($k));
                $params[] = $k . " <= ? ";
            } elseif (strpos(strtolower($k), ' >') > 0) {
                $k = str_replace(' >', '', strtolower($k));
                $params[] = $k . " > ? ";
            } elseif (strpos(strtolower($k), ' <') > 0) {
                $k = str_replace(' <', '', strtolower($k));
                $params[] = $k . " < ? ";
            } else {
                $params[] = $k . " = ? ";
            }
        }

        return 'WHERE ' . implode('AND ', $params);
    }
}