
<?php
    /**
     * Created by PhpStorm.
     * User: fduijnho
     * Date: 22/05/2019
     * Time: 13:34
     */

    class clsConvertXml extends clsDatabase {

        private $xmlMessages = NULL;

        public function __construct () {

            //
            parent::__construct ();

            //get data from server and save to database
            $this->convertXmlData ();

        }

        private function convertXmlData() {

            //per run, we are going to create a message, if poll has been finished
            $sql = "SELECT DISTINCT run FROM soap_xml_source WHERE converted = ? AND pollReady = ?";
            $values = array(0, 1);
            $runs = $this->dbDirectSelect ($sql, $values, CONST_SOAP_DB_SELECT_ALL);

            //
            foreach ($runs as $run) {

                $id_run = (int)$run[ 'run' ];

                //we get all records that are not converted yet
                $sql = "SELECT id,run,xml_serialized FROM soap_xml_source WHERE run = ? AND converted = ? ORDER BY id ASC";
                $values = array ($id_run, 0);

                $records = $this->dbDirectSelect ($sql, $values, CONST_SOAP_DB_SELECT_ALL);

                //create xml messages for each record
                foreach ($records as $record) {
                    //ok, we got data, create the messages
                    $this->createXmlMessages ($record['xml_serialized']);
                }

                //now we got a bunch of XML data, save it
                if (!empty($this->xmlMessages)) {
                    $sql = "INSERT INTO soap_xml_result (id, run, xml_created, xml_encrypted, sent) VALUES (?,?,?,?,?)";
                    $body = $this->createXmlBody ();
                    $values = array(NULL, $id_run, $body, NULL, 0);
                    $this->dbDirectExecute ($sql, $values);

                    //set to converted
                    $sql = "UPDATE soap_xml_source SET converted = ? WHERE run = ?";
                    $values = array(1,$id_run);
                    $this->dbDirectExecute ($sql, $values);
                }
                else {
                    //set to failure
                    $sql = "UPDATE soap_xml_source SET converted = ? WHERE run = ?";
                    $values = array(2,$id_run);
                    $this->dbDirectExecute ($sql, $values);
                }

            }
        }


        private function createXmlMessage($vehicleId, $dateTimeUtc, $lat, $lon, $speed) {

            /*
            <!-- Message véhicule -->
            <Message>
                <!-- Identifiant du véhicule - obligatoire -->
                <IdForCarrier>AB-123-CD</IdForCarrier>
                <!-- Type de véhicule - obligatoire -->
                <Type>Road</Type>
                <!-- Date UTC du message - obligatoire -->
                <DateTime>2017-01-02T12:34:56Z</DateTime>
                <!-- Position du véhicule en WGS84 - obligatoire -->
                <Position>
                    <!-- Latitude au format décimal -->
                    <Latitude>48.866667</Latitude>
                    <!-- Longitude au format décimal -->
                    <Longitude>2.333333</Longitude>
                </Position>
                <!-- Vitesse du véhicule (en kilomètres par heure) - optionnel -->
                <Speed>75.0</Speed>
                <!-- Niveau de batterie du terminal (en %) - optionnel -->
                <Battery>80.0</Speed>
                <!-- Cap du véhicule - optionnel -->
                <Cap>N</Cap>
                <!-- Distance parcourue par le véhicule pour le transport (en kilomètres) - optionnel -->
                <Distance>123.0</Distance>
                <!-- Alarmes véhicule - optionnel -->
                <Alarms>
                    <!-- Alarme véhicule -->
                    <Alarm Value="true">AR</Alarm>
                    <!-- Alarme véhicule -->
                    <Alarm Value="false">BAT</Alarm>
                </Alarms>
            </Message>
            */

            $message = "\t".'<Message>'."\n";
            $message.= "\t\t".'<IdForCarrier>'.$vehicleId.'</IdForCarrier>'."\n";
            $message.= "\t\t".'<Type>Road</Type>'."\n";
            $message.= "\t\t".'<DateTime>'.$dateTimeUtc.'</DateTime>'."\n";
            $message.= "\t\t".'<Position>'."\n";
            $message.= "\t\t\t".'<Latitude>'.$lat.'</Latitude>'."\n";
            $message.= "\t\t\t".'<Longitude>'.$lon.'</Longitude>'."\n";
            $message.= "\t\t".'</Position>'."\n";
            $message.= "\t\t".'<Speed>'.$speed."</Speed>\n";
            $message.= "\t".'</Message>'."\n";

            return $message;

        }


        private function createXmlMessages($serializedResponse) {

            $response = unserialize ($serializedResponse);

            if (!empty($response)) {

                if (property_exists ($response,'return')) {

                    if (property_exists ($response->return, 'messages')) {

                        //get data from messages
                        $messages = $response->return->messages;

                        if (!empty($messages)) {

                            //if there is only 1 message it is an object
                            if (is_object($messages)) {
                                $messages = array($messages);
                            }

                            //
                            foreach ($messages as $message) {

                                //defaults
                                $vehicleId = '';
                                $lat = 0;
                                $lon = 0;
                                $dateTimeSent = date('Ymd\THisZ',time());
                                $speed = 0;

                                //none are set by default
                                $vehicleIdSet = FALSE;
                                $latSet = FALSE;
                                $lonSet = FALSE;
                                $dateTimeSentSet = FALSE;
                                $speedSet = FALSE;

                                //set data
                                if (property_exists ($message,'gps')) {

                                    //truck
                                    if (property_exists ($message->gps, 'GENERAL')) {
                                        if (property_exists ($message->gps->GENERAL, 'Destination')) {
                                            if (property_exists ($message->gps->GENERAL->Destination, '_')) {
                                                $vehicleId = $message->gps->GENERAL->Destination->_;
                                                $vehicleIdSet = TRUE;
                                            }
                                        }
                                    }

                                    //position
                                    if (property_exists ($message->gps, 'GPS')) {
                                        if (property_exists ($message->gps->GPS, 'Latitude')) {
                                            $lat = $message->gps->GPS->Latitude;
                                            $latSet = TRUE;
                                        }
                                        if (property_exists ($message->gps->GPS, 'Longitude')) {
                                            $lon = $message->gps->GPS->Longitude;
                                            $lonSet = TRUE;
                                        }
                                    }

                                    //speed, optional but requested by EOT
                                    if (property_exists ($message->gps, 'GPS')) {
                                        if (property_exists ($message->gps->GPS, 'Speed')) {
                                            $speed = $message->gps->GPS->Speed;
                                            if (is_numeric ($speed)) {
                                                $s = (double) $speed;
                                                $s = round ($s, 2);
                                                $s = number_format($s,2);
                                                $speed = $s;
                                            }
                                            $speedSet = TRUE;
                                        }
                                    }

                                    //dateTime
                                    if (property_exists ($message->gps, 'MESSAGEINFO')) {
                                        if (property_exists ($message->gps->MESSAGEINFO, 'TxTruckUtc')) {
                                            $dateTimeSent = $message->gps->MESSAGEINFO->TxTruckUtc;
                                            $dateTimeSentSet = TRUE;
                                        }

                                    }
                                }

                                //if all set, we save the message
                                if ($vehicleIdSet && $latSet && $lonSet && $dateTimeSentSet) {
                                    $this->xmlMessages .= $this->createXmlMessage ($vehicleId, $dateTimeSent, $lat, $lon, $speed);
                                }
                            }
                        }
                    }
                }
            }
        }


        private function createXmlBody() {

            $body = '<?xml version="1.0" encoding="utf-8"?>'."\n";
            $body.= '<Messages>'."\n";
            $body.= $this->xmlMessages;
            $body.= '</Messages>'."\n";
            //$body.= '</xml>'."\n";
            return $body;

        }

    }