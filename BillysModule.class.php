<?php

require_once APPLICATION_PATH . '/resources/ActiveCollabModuleModel.class.php';

/**
 * Invoicing module defintiion
 *
 * @package activeCollab.modules.billys
 * @subpackage models
 */
class BillysModule extends AngieModule
{

    /**
     * Short module name (should be the same as module directory name)
     *
     * @var string
     */
    protected $name = 'billys';

    /**
     * Module version
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Return module name (displayed in activeCollab administration panel)
     *
     * @return string
     */
    function getDisplayName()
    {
        return lang('Billys');
    }

    /**
     * Return module description (displayed in activeCollab administration panel)
     *
     * @return string
     */
    function getDescription()
    {
        return lang('Billys Billing invoice notifications module.');
    }

    /**
     * List events that this module listens to and define event handlers
     */
    /**
     * Define event handlers
     */
    function defineHandlers()
    {
        EventsManager::listen('on_admin_panel', 'on_admin_panel');
        EventsManager::listen('on_object_deleted', 'on_object_deleted');
        EventsManager::listen('on_object_options', 'on_object_options');
     //   EventsManager::listen('on_post_install', 'on_post_install');
    } // defineHandlers

    /**
     * List routes defined and used by this module
     */
    function defineRoutes()
    {
        Router::map('billys_settings', 'billys/settings', array('controller' => 'billys', 'action' => 'settings'));
        Router::map('billys_match_organization', 'billys/match-organization', array('controller' => 'billys', 'action' => 'match_organization'));
        Router::map('billys_synchronize_companies', 'billys/synchronize-companies', array('controller' => 'billys', 'action' => 'synchronize_companies'));
        Router::map('billys_synchronize_contacts', 'billys/synchronize-contacts', array('controller' => 'billys', 'action' => 'synchronize_contacts'));
        Router::map('billys_see_contacts', 'billys/see-contacts', array('controller' => 'billys', 'action' => 'see_contacts'));
        Router::map('billys_synchronize_job_types', 'billys/synchronize-job-types', array('controller' => 'billys', 'action' => 'synchronize_job_types'));
        Router::map('billys_invoices', 'billys/invoices', array('controller' => 'billys', 'action' => 'get_invoices'));
        Router::map('billys_delete_organizations', 'billys/delete-organizations', array('controller' => 'billys', 'action' => 'delete_organizations'));
        Router::map('billys_accounts', 'billys/synchronize-accounts', array('controller' => 'billys', 'action' => 'synchronize_accounts'));
        Router::map('billys_see_accounts', 'billys/see-accounts', array('controller' => 'billys', 'action' => 'see_accounts'));
        Router::map('billys_see_job_types', 'billys/see-job-types', array('controller' => 'billys', 'action' => 'see_job_types'));
        Router::map('billys_billing_set_seller', 'billys/:account_id/set-seller', array('controller' => 'billys', 'action' => 'set_seller'), array('account_id' => Router::MATCH_ID));

    }

    /**
     * Add the custom fields required for this module
     */
    function createCustomOptions()
    {
        ConfigOptions::addOption('billys_billing_api_key');
        ConfigOptions::addOption('billys_billing_secret');
        ConfigOptions::addOption('billys_billing_email');
        ConfigOptions::addOption('billys_billing_password');
        ConfigOptions::addOption('billys_billing_user_id');
        ConfigOptions::addOption('billys_billing_job_type_seller_accountId');
        ConfigOptions::addOption('billys_billing_job_type_seller_organizationId');
    }

    /**
     * Creating the Billi's Billings invoices table.
     */
    function createTableBillysInvoices()
    {
        $primary = DBIntegerColumn::create('invoice_object_id')->setLenght(11)->setUnsigned(true);
        $newTable = DB::createTable(TABLE_PREFIX . 'billys_invoices')->addColumns(array(
            $primary,
            DBStringColumn::create('invoiceId', 22),
            DBEnumColumn::create('status', array('issued', 'paid', 'cancelled')),
            DBIntegerColumn::create('confirmed', 1),
        ));
        $index = new DBIndex('invoice_object_id', DBIndex::PRIMARY, $primary);

        $newTable->addIndex($index);
        $newTable->save();
    }

    /**
     * Creating the JobType to Product association (JobType - in ActiveCollab, Product in Billy' Billing )
     * The product id for Billy's Billing is a string
     */
    function createTableBillysJobTypes()
    {
        $jobTypeId = DBIntegerColumn::create('job_type_id')->setLenght(11)->setUnsigned(true);
        $projectId = DBIntegerColumn::create('project_id')->setLenght(11)->setUnsigned(true);
        $newTable = DB::createTable(TABLE_PREFIX . 'billys_job_types')->addColumns(array(
            DBIdColumn::create(),
            $jobTypeId,
            $projectId,
            DBStringColumn::create('productId', 22)
        ));

        $uniqueIndex = new DBIndex('unique_job_type_product', DBIndex::UNIQUE, array($jobTypeId, $projectId));
        $newTable->addIndex($uniqueIndex);
        $newTable->save();
    }

    /**
     * Creating the Company to Organization association (Company - in ActiveCollab, Organization in Billy' Billing )
     * The product id for Billy's Billing is a string
     */
    function createTableBillysCompanies()
    {
        $primary = DBIntegerColumn::create('company_id')->setLenght(11)->setUnsigned(true);
        $newTable = DB::createTable(TABLE_PREFIX . 'billys_companies')->addColumns(array(
            $primary,
            DBStringColumn::create('organizationId', 22)
        ));
        $index = new DBIndex('company_id', DBIndex::PRIMARY, $primary);

        $newTable->addIndex($index);
        $newTable->save();


    }

    /**
     * Creating the company accounts table
     * user $company_id variable name to differentiate from values that come from the Billy's Billing system
     */
    function createTableBillysCompanyAccounts()
    {
        $company_id = DBIntegerColumn::create('company_id');
        $accountId = DBStringColumn::create('accountId', 22);

        $newTable = DB::createTable(TABLE_PREFIX . 'billys_company_accounts')->addColumns(array(
            DBIdColumn::create(),
            $company_id,
            $accountId,
            DBStringColumn::create('name'),
            DBStringColumn::create('accountNo'),
            DBStringColumn::create('description'),
            DBStringColumn::create('groupId', 22),
            DBStringColumn::create('natureId'),
            DBStringColumn::create('systemRole'),
            DBStringColumn::create('currencyId', 3),
            DBStringColumn::create('taxRateId'),
            DBEnumColumn::create('isPaymentEnabled', array('true', 'false')),
            DBEnumColumn::create('isBankAccount', array('true', 'false')),
            DBEnumColumn::create('isArchived', array('true', 'false'))
        ));


        $companyIdIndex = DBIndex::create('company_id', DBIndex::KEY, array($company_id));
        $accountIdIndex = DBIndex::create('accountId', DBIndex::UNIQUE, array($accountId));
        $newTable->addIndex($companyIdIndex);
        $newTable->addIndex($accountIdIndex);
        $newTable->save();
    }

    /**
     * Creating the billys_contacts table matching ActiveCollab users with Billy's Billings contacts
     */
    function createTableBillysContacts()
    {
        $primary = DBIntegerColumn::create('company_id')->setLenght(11)->setUnsigned(true);
        $newTable = DB::createTable(TABLE_PREFIX . 'billys_contacts')->addColumns(array(
            $primary,
            DBStringColumn::create('contactId', 22),
            DBStringColumn::create('countryId', 3)
        ));
        $index = new DBIndex('company_id', DBIndex::PRIMARY, $primary);

        $newTable->addIndex($index);
        $newTable->save();
    }

    /**
     * Execute after module installation (through the interface)
     *
     * @param User $user
     */
    function postInstall(User $user)
    {
        parent::postInstall($user);
        $user->setSystemPermission('can_manage_finances', true, false);
        $user->setSystemPermission('can_manage_quotes', true, false);
        $user->save();

        $this->createCustomOptions();
        $this->createTableBillysInvoices();
        $this->createTableBillysJobTypes();
        $this->createTableBillysCompanies();
        $this->createTableBillysCompanyAccounts();
        $this->createTableBillysContacts();

    } // postInstall

    /**
     * Return object types (class names) that this module is working with
     *
     * @return array
     */
    function getObjectTypes()
    {
        return array('InvoiceStatus', 'BillysSettings');
    } // getObjectTypes

    function uninstall()
    {
        parent::uninstall();

        ConfigOptions::removeOption('billys_billing_api_key');
        ConfigOptions::removeOption('billys_billing_secret');
        ConfigOptions::removeOption('billys_billing_email');
        ConfigOptions::removeOption('billys_billing_password');
        ConfigOptions::removeOption('billys_billing_user_id');
        ConfigOptions::removeOption('billys_billing_job_type_seller_accountId');
        ConfigOptions::removeOption('billys_billing_job_type_seller_organizationId');

        if (DB::tableExists(TABLE_PREFIX . 'billys_invoices')) {
            DB::dropTable(TABLE_PREFIX . 'billys_invoices');
        }

        if (DB::tableExists(TABLE_PREFIX . 'billys_job_types')) {
            DB::dropTable(TABLE_PREFIX . 'billys_job_types');
        }

        if (DB::tableExists(TABLE_PREFIX . 'billys_companies')) {
            DB::dropTable(TABLE_PREFIX . 'billys_companies');
        }

        if (DB::tableExists(TABLE_PREFIX . 'billys_company_accounts')) {
            DB::dropTable(TABLE_PREFIX . 'billys_company_accounts');
        }
        if (DB::tableExists(TABLE_PREFIX . 'billys_contacts')) {
            DB::dropTable(TABLE_PREFIX . 'billys_contacts');
        }


    }
}