<?php

class BillysSettings extends DataObject
{

    protected $encryption_key;

    /**
     * Name of the settings table
     * @var string
     */
    protected $table_name = 'billys_settings';

    /**
     * Table fields of billys_settings
     * @var array
     */
    protected $fields = array('id', 'api_key', 'api_secret', 'email', 'password');

    /**
     * Primary key
     * @var array
     */
    protected $primary_key = array('id');

    /**
     * Auto increment field
     * @var string
     */
    protected $auto_increment = 'id';

    /**
     * @param null $id
     */
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->encryption_key = $this->getEncryptionsKey();
    }

    function getModelName($underscore = false, $singular = false)
    {
        return $underscore ? 'billys_settings' : 'BillysSettings';
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
    } // getTableName

    /**
     * Return class name of a single instance
     *
     * @return string
     */
    static function getInstanceClassName()
    {
        return 'BillysSettings';
    } // getInstanceClassName

    /**
     * Return whether instance class name should be loaded from a field, or based on table name
     *
     * @return string
     */
    static function getInstanceClassNameFrom()
    {
        return DataManager::CLASS_NAME_FROM_TABLE;
    } // getInstanceClassNameFrom

    /**
     * Return name of the field from which we will read instance class
     *
     * @return string
     */
    static function getInstanceClassNameFromField()
    {
        return '';
    } // getInstanceClassNameFrom

    /**
     * Return name of this model
     *
     * @return string
     */
    static function getDefaultOrderBy()
    {
        return '';
    } // getDefaultOrderBy

    public function getId()
    {
        return $this->getFieldValue('id');
    } // getId

    public function setId($value)
    {
        return $this->setFieldValue('id', $value);
    } // setId

    /**
     * Blowfish encryption
     * @param $value
     * @param $key
     * @return string encrypted
     */
    public static function encrypt($value, $key)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_value = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $value, MCRYPT_MODE_CBC, $iv);
        return bin2hex($iv . $encrypted_value);
    }

    /**
     * Blowfish decryption
     * @param $value
     * @param $key
     * @return string decrypted
     */
    public static function decrypt($value, $key)
    {
        $iv = pack("H*", substr($value, 0, 16));
        $x = pack("H*", substr($value, 16));
        $decrypted_value = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $x, MCRYPT_MODE_CBC, $iv);
        return trim($decrypted_value);

    }

    /**
     * Getting the encryption key
     * @return string encryption key from file
     */

    public static function getEncryptionKey()
    {
        $key = 'billys';
        if (is_file('key')) {
            $key = file_get_contents('key');
        } else {
            file_put_contents('key', $key);
        }
        return $key;
    }

    /**
     * Setting the encryption key
     * @param $key
     */
    public function setEncryptionKey($key)
    {
        $this->encryption_key = $key;
        file_put_contents('key', $key);
    }

    /**
     * Get the BillysBilling API key
     * @return string
     */
    public function getApiKey()
    {
        $api_key = $this->getFieldValue('api_key');
        return $this->decrypt($api_key, $this->encryption_key);
    }

    /**
     * Set the BillysBilling API key
     * @param $value
     * @return mixed
     */
    public function setApiKey($value)
    {
        $api_key = $this->encrypt($value, $this->encryption_key);
        return $this->setFieldValue('api_key', $api_key);
    }

    /**
     * Get email for BillysBilling authentication
     * @return string
     */
    public function getEmail()
    {

        $api_key = $this->getFieldValue('email');
        return $this->decrypt($api_key, $this->encryption_key);
    }

    /**
     * Set email for BillysBilling authentication
     * @param $value
     * @return mixed
     */
    public function setEmail($value)
    {
        $api_key = $this->encrypt($value, $this->encryption_key);
        return $this->setFieldValue('email', $api_key);
    }

    /**
     * Get password for BillysBilling authentication
     * @return string
     */
    public function getPassword()
    {
        $api_key = $this->getFieldValue('password');
        return $this->decrypt($api_key, $this->encryption_key);
    }

    /**
     * Set password for BillysBilling authentication
     * @param $value
     * @return mixed
     */
    public function setPassword($value)
    {
        $api_key = $this->encrypt($value, $this->encryption_key);
        return $this->setFieldValue('password', $api_key);
    }


}