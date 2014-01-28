<?php

class BillysInvoice extends DataObject
{

    const ISSUED = 'issued';
    const PAID = 'paid';
    const CANCELLED = 'cancelled';

    /**
     * Name of the settings table
     * @var string
     */
    protected $table_name = 'billys_invoices';

    /**
     * Table fields of billys_invoice
     * @var array
     */
    protected $fields = array('invoice_object_id', 'status', 'confirmed', 'invoiceId');

    /**
     * Primary key
     * @var array
     */
    protected $primary_key = array('invoice_object_id');


    public function __construct($invoice_object_id)
    {
        parent::__construct($invoice_object_id);

        $id = $this->getId();
        if (!$id) {
            $this->setFieldValue('invoice_object_id', $invoice_object_id);
        }
    }

    /**
     * @param bool $underscore
     * @param bool $singular
     * @return string
     */

    function getModelName($underscore = false, $singular = false)
    {
        return $underscore ? 'billys_invoice' : 'BillysInvoice';
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
        return $this->getFieldValue('invoice_object_id');
    }

    public function getStatus()
    {
        return $this->getFieldValue('status');
    }

    public function setPaid()
    {
        return $this->setFieldValue('status', self::PAID);
    }

    public function setIssued()
    {
        return $this->setFieldValue('status', self::ISSUED);
    }

    public function setCancelled()
    {
        return $this->setFieldValue('status', self::CANCELLED);
    }

    public function setConfirmed($value)
    {
        return $this->setFieldValue('confirmed', $value);
    }

    public function getConfirmed()
    {
        return $this->getFieldValue('confirmed');
    }

    public function setInvoiceId($billysInvoiceId)
    {
        return $this->setFieldValue('invoiceId', $billysInvoiceId);
    }

    public function getInvoiceId()
    {
        return $this->getFieldValue('invoiceId');
    }


    /*
    public function notifyBilly($invoice)
    {
        if (!($invoice instanceof Invoice)) {
            if (is_numeric($invoice)) {
                $invoice = new Invoice($invoice);
            }
        }


    }
    */
} 