<?php

    include (realpath (__DIR__.'/..').'/include/common.php');

    class clsTest extends clsDatabase {

        public function __construct () {

            //
            parent::__construct ();


        }

        public function printXML() {

            $sql = "SELECT xml_created FROM soap_xml_result";
            $values = array();
            $result = $this->dbDirectSelect($sql,$values);
            print_r($result);

        }



    }

    $oTest = new clsTest();
    $oTest->printXML ();