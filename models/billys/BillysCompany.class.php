<?php

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 20/01/14
 * Time: 12.20
 */
class BillysCompany extends DataObject
{

    /**
     * Name of the settings table
     * @var string
     */
    protected $table_name = 'billys_companies';

    /**
     * Table fields of billys_company
     * @var array
     */
    protected $fields = array('company_id', 'organizationId');

    /**
     * Primary key
     * @var array
     */
    protected $primary_key = array('company_id');


    public function __construct($company_id)
    {
        parent::__construct($company_id);

        $id = $this->getId();
        if (!$id) {
            $this->setFieldValue('company_id', $company_id);
        }
    }

    /**
     * @param bool $underscore
     * @param bool $singular
     * @return string
     */

    function getModelName($underscore = false, $singular = false)
    {
        return $underscore ? 'billys_company' : 'BillysCompany';
    } // getModelName

    /**
     * Return name of the table where system will persist model instances
     *
     * @param boolean $with_prefix
     * @return string
     */
    function getTableName($with_prefix = true)
    {
        return $with_prefix ? TABLE_PREFIX . $this->table_name : $this->table_name;
    } // getTableNa

    function getId()
    {
        return $this->getFieldValue('company_id');
    }


    function getOrganizationId()
    {
        return $this->getFieldValue('organizationId');
    }

    function setOrganizationId($organizationId)
    {
        return $this->setFieldValue('organizationId', $organizationId);
    }

    /**
     * Get the companies which do NOT already have an organizationId given by Billy's Billing
     * @return DbResult
     */

    function getAccounts()
    {
        $accounts = DB::execute('SELECT * FROM ' . TABLE_PREFIX . 'billys_company_accounts WHERE company_id=?', $this->getId());
        if ($accounts) {
            return $accounts->toArray();
        } else {
            return false;
        }
    }

    static function getAssociatedAccounts($company_id)
    {
        $accounts = DB::execute('SELECT * FROM ' . TABLE_PREFIX . 'billys_company_accounts WHERE company_id=?', $company_id);
        if ($accounts) {
            return $accounts->toArray();
        } else {
            return false;
        }
    }

    static function getAllUnassociatedCompanies()
    {
        return DB::execute("SELECT *
                         FROM " . TABLE_PREFIX . "companies
                         LEFT JOIN " . TABLE_PREFIX . "billys_companies ON " . TABLE_PREFIX . "billys_companies.company_id=" . TABLE_PREFIX . "companies.id WHERE " . TABLE_PREFIX . "billys_companies.company_id is NULL");
    }

    static function getAllAssociatedCompanies()
    {
        return DB::execute("SELECT *
                         FROM " . TABLE_PREFIX . "companies
                         JOIN " . TABLE_PREFIX . "billys_companies ON " . TABLE_PREFIX . "billys_companies.company_id=" . TABLE_PREFIX . "companies.id WHERE " . TABLE_PREFIX . "billys_companies.company_id IS NOT NULL");

    }

    /**
     * Returns the Billy's Billing organizationId of the given company
     * @param $company
     * @return mixed
     */
    static function getCompanyOrganizationId($company)
    {
        if ($company instanceof Company) {
            $company = $company->getId();

        }
        $result = DB::execute("SELECT organizationId
                         FROM " . TABLE_PREFIX . "billys_companies WHERE company_id= ?", $company);
        if (!empty($result)) {
            $result = $result->getRowAt(0);
            return $result['organizationId'];
        } else {
            return false;
        }

    }


} 