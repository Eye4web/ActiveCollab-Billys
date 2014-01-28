<?php

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21/01/14
 * Time: 18.10
 */
class BillysCompanyAccount extends DataObject
{
    /**
     * Name of the accounts table
     * @var string
     */
    protected $table_name = 'billys_company_accounts';

    /**
     * Table fields of billys_company_accounts
     * @var array
     *
     * used company_id instead of companyId to point the fact that this field is not from the Billy's Billing response, but from the local system
     */
    protected $fields = array(
        'id',
        'accountId',
        'company_id',
        'name',
        'accountNo',
        'description',
        'groupId',
        'natureId',
        'systemRole',
        'currencyId',
        'taxRateId',
        'isPaymentEnabled',
        'isBankAccount',
        'isArchived'
    );

    /**
     * Primary key
     * @var array
     */
    protected $primary_key = array('id');
    protected $autoincrement = array('id');

    /**
     * @param bool $underscore
     * @param bool $singular
     * @return string
     */

    function getModelName($underscore = false, $singular = false)
    {
        return $underscore ? 'billys_company_account' : 'BillysCompanyAccount';
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


    public function __construct($id = null, $company_id = null)
    {

        if (!$id) { //sorry - had to do this. Otherwise it was always giving an exception
            DB::execute('INSERT INTO ' . TABLE_PREFIX . $this->table_name . ' (company_id) VALUES(?)', $company_id);
            $id = DB::lastInsertId();
        }
        parent::__construct($id);

    }

    function getId()
    {
        return $this->getFieldValue('id');
    }

    function setId($id)
    {
        return $this->getFieldValue('id', $id);
    }

    /**
     * @param $accountNo
     * @return mixed
     */
    public function setAccountNo($accountNo)
    {
        return $this->setFieldValue('accountNo', $accountNo);
    }

    /**
     * @return mixed
     */
    public function getAccountNo()
    {
        return $this->getFieldValue('accountNo');
    }

    /**
     * @param $company_id
     * @return mixed
     */
    public function setCompany_id($company_id)
    {
        return $this->setFieldValue('company_id', $company_id);
    }

    /**
     * @return mixed
     */
    public function getCompany_id()
    {
        return $this->getFieldValue('company_id');
    }

    /**
     * @param $currencyId
     * @return mixed
     */
    public function setCurrencyId($currencyId)
    {
        return $this->setFieldValue('currencyId', $currencyId);
    }

    /**
     * @return mixed
     */
    public function getCurrencyId()
    {
        return $this->getFieldValue('currencyId');
    }

    /**
     * @param $description
     * @return mixed
     */
    public function setDescription($description)
    {
        return $this->setFieldValue('description', $description);
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->getFieldValue('description');
    }

    /**
     * @param $groupId
     * @return mixed
     */
    public function setGroupId($groupId)
    {
        return $this->setFieldValue('groupId', $groupId);
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->getFieldValue('groupId');
    }

    /**
     * @param $isArchived
     * @return mixed
     */
    public function setIsArchived($isArchived)
    {
        // $isArchived = $isArchived ? 1 : 0;
        return $this->setFieldValue('isArchived', $isArchived);
    }

    /**
     * @return mixed
     */
    public function getIsArchived()
    {
        return $this->getFieldValue('isArchived');
    }

    /**
     * @param $isBankAccount
     * @return mixed
     */
    public function setIsBankAccount($isBankAccount)
    {
        // $isBankAccount = $isBankAccount ? 1 : 0;
        return $this->setFieldValue('isBankAccount', $isBankAccount);
    }

    /**
     * @return mixed
     */
    public function getIsBankAccount()
    {
        return $this->getFieldValue('isBankAccount');
    }

    /**
     * @param $isPaymentEnabled
     * @return mixed
     */
    public function setIsPaymentEnabled($isPaymentEnabled)
    {
        // $isPaymentEnabled = $isPaymentEnabled ? 1 : 0;
        return $this->setFieldValue('isPaymentEnabled', $isPaymentEnabled);
    }

    /**
     * @return mixed
     */
    public function getIsPaymentEnabled()
    {
        return $this->getFieldValue('isPaymentEnabled');
    }

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name)
    {
        return $this->setFieldValue('name', $name);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getFieldValue('name');
    }

    /**
     * @param $natureId
     * @return mixed
     */
    public function setNatureId($natureId)
    {
        return $this->setFieldValue('natureId', $natureId);
    }

    /**
     * @return mixed
     */
    public function getNatureId()
    {
        return $this->getFieldValue('natureId');
    }


    /**
     * @param $systemRole
     * @return mixed
     */
    public function setSystemRole($systemRole)
    {
        $systemRole = $systemRole ? $systemRole : "";
        return $this->setFieldValue('systemRole', $systemRole);
    }

    /**
     * @return mixed
     */
    public function getSystemRole()
    {
        return $this->getFieldValue('systemRole');
    }

    /**
     * @param $taxRateId
     * @return mixed
     */
    public function setTaxRateId($taxRateId)
    {
        $taxRateId = $taxRateId ? $taxRateId : '';
        return $this->setFieldValue('taxRateId', $taxRateId);
    }

    /**
     * @return mixed
     */
    public function getTaxRateId()
    {
        return $this->getFieldValue('taxRateId');
    }

    public function setAccountId($accountId)
    {
        return $this->setFieldValue('accountId', $accountId);
    }

    public function getAccountId()
    {
        return $this->getFieldValue('accountId');
    }

    public function getAllAccounts()
    {
        $accounts = DB::execute('SELECT * FROM ' . TABLE_PREFIX . 'billys_company_accounts');
        return $accounts->toArray();
    }

    public function getBillysCompany()
    {
        return new BillysCompany($this->getCompany_id());
    }

    public function setFromBillysResponseBodySingleObject(&$responseBodySingleObject)
    {

        $this->setAccountId($responseBodySingleObject->id);
        $this->setName($responseBodySingleObject->name);
        $this->setAccountNo($responseBodySingleObject->accountNo);
        $this->setDescription($responseBodySingleObject->description);
        $this->setGroupId($responseBodySingleObject->groupId);
        $this->setNatureId($responseBodySingleObject->natureId);
        $this->setSystemRole($responseBodySingleObject->systemRole);
        $this->setCurrencyId($responseBodySingleObject->currencyId);
        $this->setTaxRateId($responseBodySingleObject->taxRateId);
        $this->setIsPaymentEnabled($responseBodySingleObject->isPaymentEnabled);
        $this->setIsBankAccount($responseBodySingleObject->isBankAccount);
        $this->setIsArchived($responseBodySingleObject->isArchived);

        //die(var_dump($this->getAttributes()));
    }

    public static function count()
    {
        $count = DB::execute('SELECT COUNT(id) as count FROM ' . TABLE_PREFIX . 'billys_company_accounts');
        $count = $count->getRowAt(0);
        return $count['count'];
    }

} 