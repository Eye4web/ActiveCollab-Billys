<?php

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 23/01/14
 * Time: 13.40
 */
class BillysContact extends DataObject
{

    /**
     * Name of the settings table
     * @var string
     */
    protected $table_name = 'billys_contacts';

    /**
     * Table fields of billys_contacts
     * @var array
     */
    protected $fields = array('company_id', 'contactId','countryId');

    /**
     * Primary key
     * @var array
     */
    protected $primary_key = array('company_id');


    public function __construct($user_id)
    {
        parent::__construct($user_id);

        $id = $this->getId();
        if (!$id) {
            $this->setFieldValue('company_id', $user_id);
        }
    }

    /**
     * @param bool $underscore
     * @param bool $singular
     * @return string
     */

    function getModelName($underscore = false, $singular = false)
    {
        return $underscore ? 'billys_contact' : 'BillysContact';
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

    function setId($id)
    {
        return $this->setFieldValue('company_id', $id);
    }

    function setContactId($contactId)
    {
        return $this->setFieldValue('contactId', $contactId);
    }

    function  getContactId()
    {
        return $this->getFieldValue('contactId');
    }

    function setCountryId($countryId)
    {
        return $this->setFieldValue('countryId', $countryId);
    }

    function  getCountryId()
    {
        return $this->getFieldValue('countryId');
    }

    static function getCompanyContactId($company)
    {
        if ($company instanceof Company) {
            $company = $company->getId();
        }
        if (is_numeric($company)) {
            $billysContact = new BillysContact($company);
            return $billysContact->getContactId();
        } else {
            throw new \Exception('$user must be an instance of User or a numeric value corespondent to an existing user.id');
        }
    }

    static function getAllContacts()
    {
        $contacts = DB::execute("SELECT *
                         FROM " . TABLE_PREFIX . "companies
                         JOIN " . TABLE_PREFIX . "billys_contacts ON " . TABLE_PREFIX . "billys_contacts.company_id=" . TABLE_PREFIX . "companies.id WHERE " . TABLE_PREFIX . "billys_contacts.company_id IS NOT NULL");

        if ($contacts) {
            return $contacts->toArray();
        } else {
            return false;
        }
    }

    static function getAllUnassociatedContacts()
    {
        return DB::execute("SELECT *
                         FROM " . TABLE_PREFIX . "companies
                         LEFT JOIN " . TABLE_PREFIX . "billys_contacts ON " . TABLE_PREFIX . "billys_contacts.company_id=" . TABLE_PREFIX . "companies.id WHERE " . TABLE_PREFIX . "billys_contacts.company_id is NULL");
    }

    static function getAllAssociatedContacts()
    {
        return DB::execute("SELECT *
                         FROM " . TABLE_PREFIX . "companies
                         JOIN " . TABLE_PREFIX . "billys_contacts ON " . TABLE_PREFIX . "billys_contacts.company_id=" . TABLE_PREFIX . "companies.id WHERE " . TABLE_PREFIX . "billys_contacts.company_id IS NOT NULL");

    }
} 