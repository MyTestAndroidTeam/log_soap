<?php
    /**
     * Created by PhpStorm.
     * User: fduijnho
     * Date: 22/05/2019
     * Time: 12:36
     */

    //CONFIGURE AND CHECK!
    //define ('SOAP_WSDL_URL','https://dekker-api.easyiq.nl/soap/wsdl/wsdl.xml');
    define ('SOAP_WSDL_URL','https://platform-api.chdekker.nl/soap/wsdl/wsdl.xml');
    //please check in shell with gpg --list-keys
    define ('GPG_KEY','EF2C31012F8FA8AEE9125C9FD199150357C6FE04');
    //important; else GPG does not find the key; set this to the user the script runs under
    //perform gpg --help | grep home to find the home directory
    define ('DIR_SOAP_GPG_HOME','/home/fduijnho/.gnupg');

    //MYSQL CONFIG
    define ('CONST_SOAP_MYSQL_HOST', 'localhost');
    define ('CONST_SOAP_MYSQL_PORT', '3306');
    define ('CONST_SOAP_MYSQL_USER', 'chdekker');
    define ('CONST_SOAP_MYSQL_PASSWORD', 'jf45Efgda8211jNXSVoIUdf');
    define ('CONST_SOAP_MYSQL_DATABASE', 'chdekker');

    //SHOULD BE FINE
    define ('GPG_SIGNATURE_FILE', DIR_SOAP_INCLUDE.'/DEKKER_20171120_D199150357C6FE04.gpg');
    define ('GPG_KEY_PASS', 'bdb5cb18-f729-4fff-8aab-45c8a3dcb801');
    define ('MAX_EXECUTION_TIME',60);
    define ('DAHER_FTP_SERVER','82.127.6.40');
    define ('DAHER_FTP_USER','dekker');
    define ('DAHER_FTP_PASSWORD','935LD3Mnf7Gf');
    define ('DAHER_FTP_FILENAME_PREFIX','dekker_route_');
    define ('DAHER_FTP_FILENAME_SUFFIX','.xml.gpg');
