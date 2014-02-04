<?php

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 20/01/14
 * Time: 09.22
 */
class BillysRequest
{

    static public function send($method, $url, $body = null)
    {

        $billysConfig = ConfigOptions::getValue(array(
            'billys_billing_email',
            'billys_billing_password'
        ));

        $encryptionKey = BillysSettings::getEncryptionKey();
        $email = BillysSettings::decrypt($billysConfig['billys_billing_email'], $encryptionKey);
        $password = BillysSettings::decrypt($billysConfig['billys_billing_password'], $encryptionKey);

        $c = curl_init("https://api.billysbilling.com/v2" . $url);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($c, CURLOPT_USERPWD, $email . ":" . $password);
        if ($body) {
            curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($c, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        }
        $res = curl_exec($c);
        $body = json_decode($res);
        $info = curl_getinfo($c);
        return (object)array(
            'status' => $info['http_code'],
            'body' => $body
        );
    }

    /**
     * Needs an API Key - won't work otherwise
     *
     * @param $method
     * @param $url
     * @param null $body
     * @return object
     */

    /*
    static public function sendV1($method, $url, $body = null)
    {
        $billysConfig = ConfigOptions::getValue(array(
            'billys_billing_email',
            'billys_billing_password'
        ));

        $accessToken='9ee3f61c586b216fe84cda61d359dc0d56aaff19';

        $encryptionKey = BillysSettings::getEncryptionKey();
        $email = BillysSettings::decrypt($billysConfig['billys_billing_email'], $encryptionKey);
        $password = BillysSettings::decrypt($billysConfig['billys_billing_password'], $encryptionKey);

        $c = curl_init("https://api.billysbilling.dk/v1" . $url);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($c, CURLOPT_USERPWD, $accessToken . ":" . $password);
        if ($body) {
            curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($c, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        }
        $res = curl_exec($c);
        $body = json_decode($res);
        $info = curl_getinfo($c);
        return (object)array(
            'status' => $info['http_code'],
            'body' => $body
        );
    }
*/
    static public function setIssued(Invoice $invoice, User $user)
    {

    }

    static public function setPaid(Invoice $invoice, User $user)
    {

    }

    static public function setCancelled(Invoice $invoice, User $user)
    {

    }

    static public function edit(Invocie $invocie)
    {

    }

    static public function listInvoices()
    {
        $response = self::send('GET', '/invoices?organizationId=7YbTWBl2S6yRlW1TLR1smw');
        echo '<pre>';
        var_dump($response);
        die();
    }

    static public function createOrganization()
    {
        $response = self::send('POST', '/invocie');
    }

    static public function jobTypeToProductAssociation(JobType &$jobType, BillysJobType &$billysJobType)
    {

    }


}

