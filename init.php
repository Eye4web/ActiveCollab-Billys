<?php

/**
 * Billys module initialisation file
 */

const BILLYS_MODULE = 'billys';
const BILLYS_MODULE_PATH = __DIR__;

AngieApplication::usePackage('database');

AngieApplication::setForAutoload(array(
    'BillysSettings' => BILLYS_MODULE_PATH . '/models/billys/BillysSettings.class.php',
    'BillysCompany' => BILLYS_MODULE_PATH . '/models/billys/BillysCompany.class.php',
    'BillysContact' => BILLYS_MODULE_PATH . '/models/billys/BillysContact.class.php',
    'BillysInvoice' => BILLYS_MODULE_PATH . '/models/billys/BillysInvoice.class.php',
    'BillysJobType' => BILLYS_MODULE_PATH . '/models/billys/BillysJobType.class.php',
    'BillysRequest' => BILLYS_MODULE_PATH . '/models/billys/BillysRequest.class.php',
    'BillysCompanyAccount' => BILLYS_MODULE_PATH . '/models/billys/BillysCompanyAccount.class.php'

));