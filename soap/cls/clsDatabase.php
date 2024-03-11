<?php
    /**
     * Created by PhpStorm.
     * User:      fduijnho
     * Author:    Frans-Willem Duijnhouwer
     * Copyright: C.H.Dekker
     */

    class clsDatabase {

        /**
         * @var PDO
         */
        protected $conn;

        public function __construct () {

            $this->connect ();

        }

        public function __destruct () {

            $this->disconnect ();

        }


        public function connect () {

            try {
                $this->conn = new PDO('mysql:host=' . CONST_SOAP_MYSQL_HOST . ';dbname=' . CONST_SOAP_MYSQL_DATABASE, CONST_SOAP_MYSQL_USER, CONST_SOAP_MYSQL_PASSWORD);
                $this->conn->setAttribute (PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8;SET time_zone = "UTC"');
                $this->conn->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute (PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->conn->setAttribute (PDO::ATTR_STRINGIFY_FETCHES, FALSE);
                $this->conn->setAttribute (PDO::ATTR_PERSISTENT, FALSE);
            }
            catch (PDOException $e) {
                die ('could not connect to MySQL database ' . CONST_SOAP_MYSQL_DATABASE . ' on ' . CONST_SOAP_MYSQL_HOST . ' (Exception: ' . $e . ')');
            }

        }


        public function disconnect () {

            unset($this->conn);

        }

        public function dbDirectSelect ($strSql, $aValues, $selectType = CONST_SOAP_DB_SELECT_ROW) {

            //go
            $result = FALSE;
            try {
                $stmt = $this->conn->prepare ($strSql);
                $stmt->execute ($aValues);
                if ($selectType == CONST_SOAP_DB_SELECT_ROW) {
                    $result = $stmt->fetch ();
                }
                else {
                    $result = $stmt->fetchAll ($selectType);
                }
            }
            catch (Exception $e) {
                echo "Exception ".$e." on select query";
            }

            return $result;

        }


        public function dbDirectExecute ($strSql, $aValues) {

            //every change is its own transaction
            $this->conn->beginTransaction ();

            //go
            $result = FALSE;
            try {
                $stmt = $this->conn->prepare ($strSql);
                $result = $stmt->execute ($aValues);
            }
            catch (Exception $e) {
                echo "Exception ".$e." on executing query";
            }

            //commit
            if ($result) {
                $this->conn->commit ();
            }
            else {
                $this->conn->rollBack ();
            }

            return $result;

        }

    }