#Installation

Magento2 MpApi module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and MpApi(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/MpApi/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#User Guide

For Magento2 MpApi module's working process follow user guide - https://webkul.com/blog/magento2-marketplace-rest-api/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/

Sample Implementation of API for Admin
-------------------------------
<?php

session_start();
/*
 *  base url of the magento host
 */
$host = 'magentohost';
unset($_SESSION['access_token']);
if (!isset($_SESSION['access_token'])) {
    echo 'Authenticating...<br>';
    $username = 'admin';
    $password = 'admin123';
    /*
     * Authentication details of the admin
     */
    $postData['username'] = $username;
    $postData['password'] = $password;

    /*
     * Init curl
     */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host.'rest/V1/integration/admin/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.strlen(json_encode($postData)),
        )
    );
    curl_setopt($ch, CURLOPT_POST, count($postData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    $output = curl_exec($ch);
    curl_close($ch);

    /*
     * Access token in json format
     */
    echo $output;
    $_SESSION['access_token'] = $output;
}
    if (isset($_SESSION['access_token'])) {
        /*
        * Create headers for authorization
        */
        $headers = array(
            'Authorization: Bearer '.json_decode($_SESSION['access_token']),
        );

        echo '<pre>';
        echo 'api call... with key: '.$_SESSION['access_token'].'<br><br><br>';
        $ch = curl_init();
        /*
        * Set api resource url
        */
        curl_setopt($ch, CURLOPT_URL, $host.'rest/V1/mpapi/admin/sellers');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers
    );
        $output = curl_exec($ch);
        curl_close($ch);
        echo '<br>';
        /*
         * Json response need to rtrim with [], some times it is appended to the respose so the json becomes invalid so need to rtrim the response
        */
        $test = json_decode(rtrim($output, '[]'));
        echo '
        =========================RESPONSE================================<br>
        ';

        print_r($test);
    }
exit(0);

Sample Implementation of API for Customer
----------------------------------
<?php

session_start();
/*
 *  base url of the magento host
 */
$host = 'magentohost';

unset($_SESSION['access_token']);
if (!isset($_SESSION['access_token'])) {
    echo 'Authenticating...<br>';
     /*
     * Authentication details of the customer
     */
    $username = 'test@webkul.com';
    $password = 'Admin123';
    $postData['username'] = $username;
    $postData['password'] = $password;

    /*
     * Init curl
     */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host.'rest/V1/integration/customer/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    /*
     * Set content type and length
     */
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.strlen(json_encode($postData)),
        )
    );
    /*
     * Setpost data
     */
    curl_setopt($ch, CURLOPT_POST, count($postData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    $output = curl_exec($ch);
    curl_close($ch);
    /*
     * Access token in json format
     */
    echo $output;
    $_SESSION['access_token'] = $output;
}
    if (isset($_SESSION['access_token'])) {
        /*
        * Create headers for authorization
        */
        $headers = array(
            'Authorization: Bearer '.json_decode($_SESSION['access_token']),
        );
        echo '<pre>';
        echo 'api call... with key: '.$_SESSION['access_token'].'<br><br><br>';
        $ch = curl_init();
        /*
        * Set api resource url
        */
        curl_setopt($ch, CURLOPT_URL, $host.'rest/V1/mpapi/sellers/me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers
    );
        $output = curl_exec($ch);
        curl_close($ch);
        echo '<br>';
        /*
         * Json response need to rtrim with [], some times it is appended to the respose so the json becomes invalid so need to rtrim the response
        */
        $test = json_decode(rtrim($output, '[]'));
        echo '
        =========================RESPONSE================================<br>
        ';

        print_r($test);
    }
exit(0);
