<?php

namespace TinyPHP\Helper;

use PDO;

class DBHelper
{
    private $data = [];
    private $params = null;
    private $PDO;
    private $entity;
    private $classRef;
    private $table;
    private $dbname;
    private $error;

    public function __construct($config)
    {
        if (isset($config['dbname'])) {
            $this->dbname($config['dbname']);
        }
        $this->PDO = self::getPDO($config, $share);
    }

    private static function getPDO($config, $share)
    {
        $opt = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$config['charset']}';",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        );
        $pdo = new PDO($config['dsn'], $config['user'], $config['password'], $opt);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    }

    public function dbname($name)
    {
        $this->dbname = $name;
    }

    public function table($name, $fetchClass = false)
    {
        if ($fetchClass) {
            $this->entity($name);
        } else {
            $this->table = $name;
        }

        return $this;
    }

    public function entity($name)
    {
        $this->entity = $name;
        $this->classRef = new \ReflectionClass($name);
        $this->table = $this->classRef->getConstant('TABLE_NAME');

        return $this;
    }

    private function getLibName()
    {
        return "`{$this->dbname}`.`{$this->table}`";
    }

    private function select($fields)
    {
        if (is_array($fields)) {
            $strFields = '`'.implode('`,`', $fields).'`';
        } else {
            $strFields = $fields;
        }
        $query = "SELECT {$strFields} FROM {$this->getLibName()}";
        foreach ($this->data as $value) {
            $query .= "{$value}";
        }

        $statement = $this->PDO->prepare($query);
        $statement->execute($this->params);
        if ($this->entity == null) {
            $statement->setFetchMode(PDO::FETCH_ASSOC);
        } else {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }

        $this->data = [];
        $this->params = null;

        return $statement;
    }

    public function getone($field = '*')
    {
        $statement = $this->select($field);

        return $statement->fetch();
    }

    public function get($field = '*')
    {
        $statement = $this->select($field);

        return $statement->fetchAll();
    }

    public function update($v, $params = [])
    {
        $values = [];
        $update = '';
        if (is_array($v)) {
            $arr = [];
            foreach ($v as $key => $value) {
                $arr[] = '`'.$key.'`=?';
                $values[] = $value;
            }
            $update = implode(',', $arr);
        } else {
            $update = (string) $v;
            $values = $params;
        }

        if (!empty($this->params)) {
            foreach ($this->params as $value) {
                $values[] = $value;
            }
        }

        $query = "UPDATE {$this->getLibName()} SET {$update}";
        foreach ($this->data as $value) {
            $query .= "{$value}";
        }

        $statement = $this->PDO->prepare($query);
        $statement->execute($values);

        $this->data = [];
        $this->params = null;

        return $statement->rowCount();
    }

    public function insert($params)
    {
        if (is_object($params)) {
            $fnum = 0;
            $props = $this->classRef->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($props as $prop) {
                $name = $prop->getName();
                $fields[] = $name;
                $values[] = $params->$name;
                ++$fnum;
            }
        } else {
            $fields = array_keys($params);
            $values = array_values($params);
            $fnum = count($fields);
        }

        $strFields = '`'.implode('`,`', $fields).'`';
        $arr = array_fill(0, $fnum, '?');
        $strValues = implode(',', $arr);

        $query = "INSERT INTO {$this->getLibName()} ({$strFields}) VALUES ({$strValues})";
        $statement = $this->PDO->prepare($query);
        $ret = $statement->execute($values);

        $this->data = [];
        $this->params = null;

        return $this->PDO->lastInsertId();
    }

    public function delete()
    {
        $query = "DELETE FROM {$this->getLibName()}";
        foreach ($this->data as $value) {
            $query .= "{$value}";
        }

        $statement = $this->PDO->prepare($query);
        $ret = $statement->execute($this->params);

        $this->data = [];
        $this->params = null;

        return $ret;
    }

    // id = ?, [1]
    public function where($v, array $params = null)
    {
        if (is_array($v)) {
            foreach ($v as $key => $val) {
                $arr[] = "`{$key}`={$val}";
            }
            $v = implode(' AND ', $arr);
        }

        if (isset($this->data['where'])) {
            $this->data['where'] .= " AND {$v}";
        } else {
            $this->data['where'] = " WHERE {$v}";
        }
        $this->params = $params;

        return $this;
    }

    public function in($field, array $params)
    {
        $strValues = implode(',', $params);
        $in = "`{$field}` IN({$strValues})";
        if (isset($this->data['where'])) {
            $this->data['where'] .= " AND {$in}";
        } else {
            $this->data['where'] = " WHERE {$in}";
        }

        return $this;
    }

    //`id` DESC
    public function orderby($v)
    {
        $this->data['orderby'] = " ORDERBY {$v}";

        return $this;
    }

    public function limit($start, $n = 0)
    {
        if ($n == 0) {
            $limit = " LIMIT {$start}";
        } else {
            $limit = " LIMIT {$start},{$n}";
        }
        $this->data['limit'] = " {$limit}";

        return $this;
    }

    public function id($id)
    {
        $this->data['where'] = " WHERE `id`={$id}";

        return $this;
    }

    public function sql($query)
    {
        $statement = $this->PDO->prepare($query);
        $statement->execute();
        if ($this->entity == null) {
            $statement->setFetchMode(PDO::FETCH_ASSOC);
        } else {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }

        return $statement->fetchAll();
    }

    public function exec($sql, array $params = null)
    {
        $statement = $this->PDO->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount();
    }

    public function begin()
    {
        $this->PDO->beginTransaction();
    }

    public function rollback()
    {
        $this->PDO->rollBack();
    }

    public function commit()
    {
        return $this->PDO->commit();
    }

    public function error()
    {
        return $this->error;
    }
}
