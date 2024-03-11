<?php
    /**
     * Created by PhpStorm.
     * User: fduijnho
     * Date: 22/05/2019
     * Time: 12:36
     */

    //get constants
    include (dirname (__FILE__) . '/constants.php');

    //get settings
    include (dirname (__FILE__) . '/config.php');

    //work in UTC
    date_default_timezone_set ('UTC');

    //timeouts
    ini_set ('max_execution_time',MAX_EXECUTION_TIME);
    set_time_limit(MAX_EXECUTION_TIME);

    //gnupg
    putenv("GNUPGHOME=".DIR_SOAP_GPG_HOME);

    //create default classes
    include (DIR_SOAP_CLS.'/clsDatabase.php');
    include (DIR_SOAP_CLS.'/clsGetXML.php');
    include (DIR_SOAP_CLS.'/clsConvertXml.php');
    include (DIR_SOAP_CLS.'/clsEncrypt.php');
    include (DIR_SOAP_CLS.'/clsFtpUpload.php');
