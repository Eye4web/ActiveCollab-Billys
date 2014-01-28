<?php

/**
 * Invoicing module on_object_options event handler
 *
 * @package activeCollab.modules.billys
 * @subpackage handlers
 */

/**
 * Source module on_object_options event handler
 *
 * @param ApplicationObject $object
 * @param IUser $user
 * @param NamedList $options
 * @param string $interface
 */

function billys_handle_on_object_options(&$object, &$user, &$options, $interface)
{
    if ($object instanceof Invoice) {

        $invoiceId = $object->getId();

        $billysInvoice = new BillysInvoice($invoiceId);

        $save = false;

        $status = $billysInvoice->getStatus();

        $issuedTo = BillysContact::getCompanyContactId($object->getCompanyId());

        $organizationId = ConfigOptions::getValue('billys_billing_job_type_seller_organizationId');
        $accountId = ConfigOptions::getValue('billys_billing_job_type_seller_accountId');

        /**
         * @var TimeRecords $invoiceTimeRecords
         * @var TimeRecord $timeRecord
         */


        if ($object->isIssued()) {

            if ($status != BillysInvoice::ISSUED && $status != BillysInvoice::PAID) {

                $invoiceTimeRecords = $object->getTimeRecords();

                $invoiceProducts = array();
                foreach ($invoiceTimeRecords as $timeRecord) {
                    //getting the products for this invoice

                    $jobType = $timeRecord->getJobType();
                    $project = $timeRecord->getProject();

                    $product = BillysJobType::getBillysJobType($timeRecord->getJobTypeId(), $project->getId());
                    $currency = $project->getCurrency()->getCode();

                    $invoiceProducts[] = array(
                        'productId' => $product->getProductId(),
                        'quantity' => $timeRecord->getValue(),
                        'unitPrice' => $jobType->getHourlyRateFor($project)
                    );
                }

                $invoice = array(
                    'organizationId' => $organizationId,
                    'entryDate' => $object->getCreatedOn()->format('Y-m-d'),
                    'dueDate' => $object->getDueOn()->format('Y-m-d'),
                    'state' => 'approved',
                    'contactId' => $issuedTo,
                    'sentState' => 'sent',
                    'currencyId' => $currency,
                    'lines' => $invoiceProducts,
                );
                $response = BillysRequest::send('POST', '/invoices', array('invoice' => $invoice));

                if ($response->body->meta->success) {
                    $billysInvoice->setIssued();
                    $billysInvoice->setInvoiceId($response->body->invoices[0]->id);
                    $billysInvoice->setConfirmed(1);
                    $billysInvoice->save();
                } else {
                    echo '<pre>There has been a problem sending this invoice. API server responded with: ' . $response->body->errorMessage . '</pre>';
                }
            }
        }
        /**
         * Do when a payment happens
         */
        if ($object->isPaid()) {

            if ($status != BillysInvoice::PAID) {

                /*
                 * POST https://api.billysbilling.com/v2/bankPayments
                 *
                 * How the message towards Billy's Billings should look like
                 * {
                 *   "bankPayment": {
                 *       "organizationId": "YOUR ORGANIZATION ID",
                 *       "contactId": "SAME AS THE INVOICE'S ID",
                 *       "entryDate": "2014-01-16",
                 *       "cashAmount": 1200,
                 *       "cashSide": "debit",
                 *       "cashAccountId": "BANK ACCOUNT ID",
                 *       "associations": [
                 *           {
                 *               "subjectReference": "invoice:inv-1234"
                 *           }
                 *       ]
                 *   }
                 * };
                */
                /*
                 * <<<<<<<<<<<<<<<<<<<<================================================= Work in progress =========|
                 *
                 * API server responds with:
                 * "We're sorry. An internal server error occurred. Support has been notified."
                 *
                 * The API specifications require the commented out fields, but when they are used the reply
                 * from the server is that those fields are read-only and can not be changed
                 * ******************************************************************************************|
                 *
                 */


                $payment = array('bankPayment' => array(
                    'organizationId' => $organizationId,
                    'contactId' => $issuedTo,
                    'entryDate' => date('Y-m-d', time()),
                    'cashAmount' => $object->getPaidAmount(),
                    'cashSide' => 'debit',
                    'cashAccountId' => $accountId,
                    'associations' => array(
                        array(
                            'subjectReference' => 'invoice:' . $billysInvoice->getInvoiceId()
                        )
                    )
                ));
                $response = BillysRequest::send('POST', '/bankPayments', $payment);
                // TO DO -- need to do somethig with the response when this will be available

                /*
                 * <<<<<<<<<<<<<<<<<<<<================================================= Work in progress =========|
                 */

                if ($response->body->meta->success) {
                    $billysInvoice->setPaid();
                    $save = true;
                } else {
                    echo '<pre>There has been a problem sending this invoice. API server responded with: ' . $response->body->errorMessage . '</pre>';
                }
            }
        }

        /**
         * Do when an invoice is cancelled
         */
        if ($object->isCanceled()) {

            if ($status != BillysInvoice::CANCELLED) {

                $response = BillysRequest::send('DELETE', '/invoices/' . $billysInvoice->getInvoiceId());

                if ($response->body->meta->success) {
                    $billysInvoice->setCancelled();
                    $save = true;
                } else {
                    echo '<pre>There has been a problem sending this invoice. API server responded with: ' . $response->body->errorMessage . '</pre>';
                }
            }
        }

        if ($save) {
            $billysInvoice->doSave();
        }

    }

    if ($object instanceof JobType) {

        error_log('it\'s a job type');
        $billysJobType = new BillysJobType($object->getId());

        $productId = $billysJobType->getProductId();

        if (empty($productId)) {
            BillysRequest::jobTypeToProductAssociation($object, $billysJobType);
        }
    }

    if ($object instanceof Company) {

    }
} // invoicing_handle_on_object_options

?>


