<?php
    /**
     * Created by PhpStorm.
     * User: fduijnho
     * Date: 22/05/2019
     * Time: 12:46
     */

    class clsFtpUpload extends clsDatabase {

        public function __construct () {

            //
            parent::__construct ();

            //upload files
            $this->uploadMessages ();

        }

        private function uploadMessages() {

            //get encrypted messages not sent
            $sql = "SELECT id, xml_encrypted FROM soap_xml_result WHERE encrypted = ? and sent = ?";
            $values = array(1,0);
            $messages = $this->dbDirectSelect ($sql, $values, CONST_SOAP_DB_SELECT_ALL);

            //encrypt xml data using Gnu public key
            foreach ($messages as $message) {

                //date + time: 20190523091342
                $micro_date = microtime();
                $date_array = explode(" ",$micro_date);
                if (isset($date_array[1])) {
                    $ts = $date_array[ 1 ];
                }
                else {
                    $ts = time();
                }
                $dateTime = date('YmdHis',$ts);

                //milli seconds
                $strMicro = '000';
                if (isset($date_array[0])) {
                    $micro = $date_array[ 0 ];
                    $micro = round ($micro * 1000, 0);
                    $strMicro = str_pad($micro, 3,STR_PAD_RIGHT);
                }

                //final stamp
                $tsFile = $dateTime.$strMicro;

                //set file name
                $fileName = DAHER_FTP_FILENAME_PREFIX.$tsFile.DAHER_FTP_FILENAME_SUFFIX;

                //start
                $data = $message['xml_encrypted'];
                $result = $this->ftpUpload ($fileName,$data);
                if ($result) {
                    //set to sent
                    $sql = "UPDATE soap_xml_result SET sent=? WHERE id=?";
                    $values = array (1, $message[ 'id' ]);
                    $result = $this->dbDirectExecute ($sql, $values);
                }
                else {
                    //set to error, could not send
                    $sql = "UPDATE soap_xml_result SET sent=? WHERE id=?";
                    $values = array (2, $message[ 'id' ]);
                    $result = $this->dbDirectExecute ($sql, $values);
                }

                //remove temp file
                @unlink(DIR_SOAP_TEMP.$fileName);
            }
        }

        private function ftpUpload($fileName, $data) {

            //set filename
            $tempFileName = DIR_SOAP_TEMP.$fileName;

            //create output file
            $res = file_put_contents (DIR_SOAP_TEMP.$fileName,$data);

            //
            $result = FALSE;

            //
            if ($res) {

                //connect to the FTP
                $conn_id = ftp_connect (DAHER_FTP_SERVER);

                //login
                $login_result = ftp_login ($conn_id, DAHER_FTP_USER, DAHER_FTP_PASSWORD);

                //set to passive
                ftp_pasv($conn_id, TRUE);

                //try to upload it
                if ($login_result) {
                    if (ftp_put ($conn_id, $fileName, $tempFileName, FTP_BINARY)) {
                        $result = TRUE;
                    }
                }

                //close FTP connection
                ftp_close ($conn_id);
            }

            //
            return $result;
        }
    }