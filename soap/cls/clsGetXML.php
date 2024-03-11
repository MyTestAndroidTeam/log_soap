<?php
    /**
     * Created by PhpStorm.
     * User: fduijnho
     * Date: 22/05/2019
     * Time: 12:35
     */

    class clsGetXML extends clsDatabase {

        public function __construct () {

            //
            parent::__construct ();

            //get data from server and save to database
            $this->getXmlData ($this->getRun());

        }

        private function getRun() {

            $sql = "SELECT run FROM soap_xml_source ORDER BY run DESC";
            $values = array();
            $result = $this->dbDirectSelect ($sql,$values);
            $run = 1;
            if (!empty($result)) {
                $run = (int) $result['run'];
                $run+=1;
            }

            return $run;

        }

        private function getXmlData($run) {

            //
            $moreAvailable = TRUE;
            $count = 1;

            //we get data a maximum of 30 times, or until we are out or data
            while ($moreAvailable && $count <= 30) {

                //update counter
                $count+=1;

                //get new data and save in the new file
                $response = $this->getXmlDataFromServer ();

                //save response
                $sql = "INSERT INTO soap_xml_source (id, run, xml_serialized, pollReady) VALUES (?, ?, ?, ?)";
                $values = array(NULL, $run, serialize($response), 0);
                $this->dbDirectExecute ($sql, $values);

                //if we got an empty response, we break the loop
                if (empty($response)) {
                    //echo "No data\n";
                    break;
                }

                //need to read more?
                if (property_exists ($response, 'return')) {
                    if (property_exists ($response->return, 'moreAvailable')) {
                        $moreAvailable = $response->return->moreAvailable;
                    }
                }

                //short timeout in the loop to relax the server a bit
                if ($moreAvailable) {
                    sleep (2);
                }

            }

            //set poll to ready
            $sql = "UPDATE soap_xml_source SET pollReady=? WHERE run=?";
            $values = array(1,$run);
            $this->dbDirectExecute ($sql, $values);



        }

        private function getXmlDataFromServer() {

            $soapclient = new SoapClient(SOAP_WSDL_URL, array ("trace" => 1, "exception" => 0));
            $params = array ('systemIdentifier' => 'CHDEKKER_EOT');
            $response = $soapclient->getMessages ($params);
            //var_dump($response);
            return $response;

        }

    }
