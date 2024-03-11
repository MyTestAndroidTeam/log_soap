<?php
    /**
     * Created by PhpStorm.
     * User: fduijnho
     * Date: 22/05/2019
     * Time: 12:46
     */

    class clsEncrypt extends clsDatabase {

        private $pubKey = NULL;

        public function __construct () {

            parent::__construct ();

            //set public key
            $this->pubKey = file_get_contents (GPG_SIGNATURE_FILE);

            //encrypt
            $this->encryptMessages ();

        }

        private function encryptMessages() {

            //get not encrypted messages
            $sql = "SELECT id, xml_created FROM soap_xml_result WHERE encrypted = ?";
            $values = array(0);
            $messages = $this->dbDirectSelect ($sql, $values, CONST_SOAP_DB_SELECT_ALL);

            //encrypt xml data using Gnu public key
            foreach ($messages as $message) {
                $encryptedString = $this->GnuPGEncrypt ($message['xml_created']);
                if (!empty($encryptedString)) {
                    $sql = "UPDATE soap_xml_result SET xml_encrypted = ?, encrypted = ? WHERE id = ?";
                    $values = array ($encryptedString, 1, $message[ 'id' ]);
                    $this->dbDirectExecute ($sql, $values);
                }
                else {
                    $sql = "UPDATE soap_xml_result SET xml_encrypted = ?, encrypted = ? WHERE id = ?";
                    $values = array ('Encryption failed', 2, $message[ 'id' ]);
                    $this->dbDirectExecute ($sql, $values);
                }
            }
        }

        private function GnuPGEncrypt($message) {

            $enc = NULL;
            $res = gnupg_init();
            $result = gnupg_import($res, $this->pubKey);
            if ($result!=FALSE) {
                $result = gnupg_addencryptkey ($res, GPG_KEY);
                if ($result) {
                    $enc = gnupg_encrypt($res, $message);
                }
            }

            //
            return $enc;

        }

    }