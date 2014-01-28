<?php

// Build on top of admin controller
AngieApplication::useController('admin', ENVIRONMENT_FRAMEWORK_INJECT_INTO);

/**
 * Main Billys controller
 *
 * @package activeCollab.modules.billys
 * @subpackage controllers
 */
class BillysController extends \AdminController
{
    /**
     * Admin settings
     */
    function settings()
    {
        $encryption_key = $this->getEncryptionKey();

        if ($this->request->isSubmitted()) {
            $settings = $this->request->post('settings');
            foreach ($settings as $key => $value) {
                $encrypted_value = $this->encrypt($value, $encryption_key);
                ConfigOptions::setValue($key, $encrypted_value);
            }
        }

        $configuration = ConfigOptions::getValue(array(
            'billys_billing_api_key',
            'billys_billing_secret',
            'billys_billing_email',
            'billys_billing_password',
        ));


        foreach ($configuration as $key => $value) {
            if (!empty($value)) {
                $configuration[$key] = $this->decrypt($value, $encryption_key);
            }
        }

        $billys_user_id = ConfigOptions::getValue('billys_billing_user_id');

        if (empty($billys_user_id) && (!empty($configuration['billys_billing_email']))) {
            $response = BillysRequest::send('GET', '/user');
            ConfigOptions::setValue('billys_billing_user_id', $response->body->user->id);
            $billys_user_id = $response->body->user->id;
        }

        $configuration['billys_user_id'] = $billys_user_id;

        $accounts_count = BillysCompanyAccount::count();
        $see_accounts = $accounts_count ? true : false;
        $job_type_count = BillysJobType::count();
        $see_job_types = $job_type_count ? true : false;
        $sync_job_types = ConfigOptions::getValue('billys_billing_job_type_seller_accountId') ? true : false;

        $this->smarty->assignByRef('see_accounts', $see_accounts);
        $this->smarty->assignByRef('see_job_types', $see_job_types);
        $this->smarty->assignByRef('sync_job_types', $sync_job_types);

        $this->smarty->assignByRef('config', $configuration);


    } // settings


    function match_organization()
    {

        if ($this->request->isSubmitted()) {

            $organizationId = $this->request->post('organizationId');
            ConfigOptions::setValue('billys_billing_job_type_seller_organizationId', $organizationId);

            $response = BillysRequest::send('GET', '/accounts?organizationId=' . $organizationId);
            $accounts = $response->body->accounts;

            $user = Authentication::getLoggedUser();
            $company = $user->getCompany();


            $associatedAccounts = BillysCompany::getAssociatedAccounts($company->getId());

            if ($associatedAccounts) {
                $associatedAccounts = array_column($associatedAccounts, 'id', 'accountId'); //getting the accountId as key again. Will be faster to check for $associatedAccounts[$account->accountId] than to find the accountId in_array($associatedAccounts);
            } else {
                $associatedAccounts = array();
            }
            $received_accounts = array();

            foreach ($accounts as $account) {
                $newAccount = null;
                if (!isset($associatedAccounts[$account->id])) {
                    $newAccount = new BillysCompanyAccount(null, $company->getId()); //adding new accounts
                    $newAccount->setCompany_id($company->getId());
                } else {
                    $newAccount = new BillysCompanyAccount($associatedAccounts[$account->id]); //getting an existing account to update it
                }

                $newAccount->setFromBillysResponseBodySingleObject($account);
                $newAccount->save();
                $received_accounts[] = array(
                    'name' => $account->name,
                    'description' => $account->description,
                    'natureId' => $account->natureId,
                    'isPaymentEnabled' => $account->isPaymentEnabled,
                    'isBankAccount' => $account->isBankAccount,
                    'isArchived' => $account->isArchived,
                    'id' => $company->getId()
                );
            }
            $title = 'Received accounts for your organization';
            $this->smarty->assignByRef('page_title', $title);
            $this->smarty->assignByRef('accounts', $received_accounts);
        } else {
            $response = BillysRequest::send('GET', '/user/organizations'); //request the organizations
            $billys_organizations = $response->body->organizations; //getting the organizations from the response body
            $organizations = array();
            foreach ($billys_organizations as $organization) {
                $organizations[] = array(
                    'name' => $organization->name,
                    'organizationId' => $organization->id,
                    'ownerUserId' => $organization->ownerUserId,
                    'contact' => $organization->email
                );
            }
            $title = 'Your organizations';
            $this->smarty->assignByRef('page_title', $title);
            $this->smarty->assignByRef('organizations', $organizations);
        }
    }


    function see_accounts()
    {

        $companiesAccounts = array();

        $user = Authentication::getLoggedUser();
        $organization = $user->getCompany();

        $company['companyName'] = $organization->getName();
        $company['accountDetails'] = BillysCompany::getAssociatedAccounts($organization->getId());
        $companiesAccounts[] = $company;

        $this->setView('synchronize_accounts');
        $this->smarty->assignByRef('companyAccounts', $companiesAccounts);
        $this->smarty->assignByRef('custom_page_title', lang('Registered accounts'));
    }


    function synchronize_contacts()
    {


        $organizationId = ConfigOptions::getValue('billys_billing_job_type_seller_organizationId');

        $contacts = BillysContact::getAllContacts();
        $response = BillysRequest::send('GET', '/contacts?organizationId=' . $organizationId);

        $billysContacts = $response->body->contacts;


        $receivedContacts = array();

        foreach ($billysContacts as $contact) {
            $receivedContacts[$contact->name] = $contact;
        }

        if ($contacts) {
            $contacts = array_column($contacts, 'contactId', 'company_id');
        }


        /**
         * @var Companies $companies
         * @var Company $company
         */
        $companies = Companies::find();

        $synchronized_contacts = array();

        foreach ($companies as $key => $company) {

            $company_name = $company->getName();
            if (!$company->isOwner()) {
                if ($company->getState() == 3) { // 3 is the state for active companies. Only do this for active companies
                    $countryCode = $this->getCountryCode($company);

                    $company_id = $company->getId();
                    $contact = array(
                        'type' => 'company',
                        'organizationId' => $organizationId,
                        'name' => $company_name,
                        'countryId' => $countryCode,
                        'contactNo' => $company_id,
                        'isCustomer' => true
                    );

                    if (isset($receivedContacts[$company_name])) { //if this company already is associated to a Billy's Billing contact, then simply update the Billy's Billing contact with the company information
                        $response = BillysRequest::send('PUT', '/contacts/' . $contacts[$company_id], array('contact' => $contact));
                        unset($receivedContacts[$company_name]); //releasing the received contact after updating. This way, after the end of this loop, if there are any contacts from Billy's Billings that are not in our system we'll store them
                    } else {
                        $response = BillysRequest::send('POST', '/contacts', array('contact' => $contact));
                    }

                    $response_contact = $response->body->contacts[0];
                    $contact['contactId'] = $response_contact->id;
                    $synchronized_contacts[] = $contact;
                    $contact = new BillysContact($response_contact->contactNo);
                    $contact->setContactId($response_contact->id);
                    $contact->setCountryId($response_contact->countryId);
                    $contact->save();
                } else {
                    if (isset($receivedContacts[$company_name])) { // if the company is inactive remove it from the received companies array, so that the next operations will not be made on it
                        unset($receivedContacts[$company_name]);
                    }
                }
            }


        }

        $time = new DateTimeValue(time());
        $user = Authentication::getLoggedUser();

        foreach ($receivedContacts as $contact) {

            //creating the new company
            $company = new Company();
            $company->setName($contact->name);
            $company->setCreatedOn($time);
            $company->setCreatedBy($user);
            $company->setState(3);
            $company->save();

            //associating it with the Billi's Billings contact
            $company_id = $company->getId();
            $billysContact = new BillysContact($company_id);
            $billysContact->setContactId($contact->id);
            $billysContact->setCountryId($contact->countryId);
            $billysContact->save();

            $synchronized_contacts[] = array(
                'type' => 'company',
                'organizationId' => $organizationId,
                'name' => $company->getName(),
                'countryId' => $billysContact->getCountryId(),
                'contactNo' => $company->getId(),
                'isCustomer' => true
            );
            EventsManager::trigger('on_shutdown');
        }

        $page_title = 'Synchronized contacts';
        $this->setView('synchronize_contacts');
        $this->smarty->assignByRef('page_title', $page_title);
        $this->smarty->assignByRef('contacts', $synchronized_contacts);
    }


    function see_contacts()
    {
        $contacts = BillysContact::getAllContacts();

        foreach ($contacts as $key => $contact) {

            $company = new Company($contact['company_id']);
            $countryCode = $this->getCountryCode($company);
            $contacts[$key]['countryId'] = $countryCode;
        }

        $page_title = 'Contacts:';
        $this->setView('synchronize_contacts');
        $this->smarty->assignByRef('page_title', $page_title);
        $this->smarty->assignByRef('contacts', $contacts);

    }


    /**
     * Set the seller Billy's Billing accountId that will be sent to Billy' Billing when creating Billy's Billing products - identified by JobTypes in ActiveCollab
     */
    function set_seller()
    {
        $account_id = $this->request->getId('account_id');
        $account = new BillysCompanyAccount($account_id);
        ConfigOptions::setValue('billys_billing_job_type_seller_accountId', $account->getAccountId()); //setting the account Id that we will need in order to create products for BillysBilling

        $this->smarty->assignByRef('custom_page_title', lang('Account set as Job Type seller:'));

        $this->smarty->assignByRef('accountDetails', $account->getAttributes());

    }

    function synchronize_job_types()
    {
        $projects = Projects::find();
        /**
         * @var Company $company
         * @var Project $project
         */

        $sellerAccount = ConfigOptions::getValue('billys_billing_job_type_seller_accountId');
        $sellerOrganization = ConfigOptions::getValue('billys_billing_job_type_seller_organizationId');

        $sysnchronizedJobTypes = array();

        foreach ($projects as $project) {

            $synchronizedJobType = array();

            $synchronizedJobType['projectName'] = $project->getName();
            $job_types = JobTypes::findForObjectsList($project);

            $currency = $project->getCurrency();
            $project_id = $project->getId();


            foreach ($job_types as $job_type) {

                $billysJobType = BillysJobType::getBillysJobType($job_type['id'], $project_id);

                $job_type = new JobType($job_type['id']);

                $price = $job_type->getCustomHourlyRateFor($project);
                if (empty($price)) {
                    $price = $job_type->getDefaultHourlyRate();
                }

                $product = array(
                    'organizationId' => $sellerOrganization,
                    'name' => $project->getName() . ' : ' . $job_type->getName(),
                    'accountId' => $sellerAccount,
                    'productNo' => $billysJobType->getId(), // We give the product a product number because there will be custom prices for different job types. They may appear as duplicates on the Billy's Billing site, but it's because of the different prices for the same job types.
                    'prices' => array(
                        array(
                            'unitPrice' => $price,
                            'currencyId' => $currency->getCode()
                        ))
                );

                $productId = $billysJobType->getProductId();
                $response = null;
                if ($productId) {
                    //if the productId exists update the Billys Billing record
                    $response = BillysRequest::send('PUT', '/products/' . $productId, array('product' => $product));
                } else {
                    //if the productId doesn't exist create a new one
                    $response = BillysRequest::send('POST', '/products', array('product' => $product));
                }

                $product = $response->body->products[0]; //The api specifications say that it supports bulk save. Tried, but it doesn't seem to work yet

                $billysJobType->setProductId($product->id);
                $billysJobType->save();

                $synchronizedJobType['jobTypes'][] = array(
                    'name' => $job_type->getName(),
                    'price' => $price,
                    'currency' => $currency->getCode()
                );

            }
            $sysnchronizedJobTypes[] = $synchronizedJobType;
        }

        $page_title = 'Synchronized job types - products';
        $this->smarty->assignByRef('projectsJobTypes', $sysnchronizedJobTypes);
        $this->smarty->assignByRef('page_title', $page_title);
    }

    function see_job_types()
    {
        $projects = Projects::find();

        $projectsJobTypes = array();

        foreach ($projects as $project) {
            $projectJobType = array();
            $projectJobType['projectName'] = $project->getName();

            $job_types = JobTypes::findForObjectsList($project);
            $currency = $project->getCurrency();

            foreach ($job_types as $job_type) {

                $job_type = new JobType($job_type['id']);

                $price = $job_type->getCustomHourlyRateFor($project);
                if (empty($price)) {
                    $price = $job_type->getDefaultHourlyRate();
                }

                $projectJobType['jobTypes'][] = array(
                    'name' => $project->getName() . ':' . $job_type->getName(),
                    'price' => $price,
                    'currency' => $currency->getCode()
                );

            }
            $projectsJobTypes[] = $projectJobType;
        }

        $page_title = 'Projects job types - products';
        $this->setView('synchronize_job_types');
        $this->smarty->assignByRef('projectsJobTypes', $projectsJobTypes);
        $this->smarty->assignByRef('page_title', $page_title);
    }


    /**
     * this was the last controller action that is used in our business case. The others are unneeded
     * functionality, but the private functions will be needed anyway
     * ======================================================================================================
     */

    function delete_organizations()
    {
        $response = BillysRequest::send('GET', '/user/organizations');
        $organizations = $response->body->organizations;
        echo '<pre>';
        foreach ($organizations as $organization) {

            $response = BillysRequest::send('DELETE', '/organizations?ids[]=' . $organization->id);
            var_dump($response);
        }
        die();
    }

    function get_invoices()
    {
        $response = BillysRequest::send('GET', '/user/organizations');
        $organizations = $response->body->organizations;
        //echo '<pre>';
        foreach ($organizations as $organization) {
            $response = BillysRequest::send('GET', '/invoices?organizationId=' . $organization->id);
            $invoices = $response->body;
            var_dump($invoices);
        }
    }


    /**
     * Controller action for synchronizing company accounts
     */
    function synchronize_accounts()
    {
        $associatedCompanies = BillysCompany::getAllAssociatedCompanies();

        $companyAccounts = array();

        foreach ($associatedCompanies as $company) {
            $response = BillysRequest::send('GET', '/accounts?organizationId=' . $company['organizationId']);
            $accounts = $response->body->accounts;

            $associatedAccounts = BillysCompany::getAssociatedAccounts($company['id']);
            if ($associatedAccounts) {
                $associatedAccounts = array_column($associatedAccounts, 'id', 'accountId'); //getting the accountId as key again. Will be faster to check for $associatedAccounts[$account->accountId] than to find the accountId in_array($associatedAccounts);
            } else {
                $associatedAccounts = array();
            }

            $newAccountStatistic['companyName'] = $company['name'];

            foreach ($accounts as $account) {
                $newAccount = null;
                if (!isset($associatedAccounts[$account->id])) {
                    $newAccount = new BillysCompanyAccount(null, $company['id']); //adding new accounts
                    $newAccount->setCompany_id($company['id']);
                } else {
                    $newAccount = new BillysCompanyAccount($associatedAccounts[$account->id]); //getting an existing account to update it
                }

                $newAccount->setFromBillysResponseBodySingleObject($account);
                $newAccount->save();
                $newAccountStatistic['accountDetails'][] = array(
                    'name' => $account->name,
                    'description' => $account->description,
                    'natureId' => $account->natureId,
                    'isPaymentEnabled' => $account->isPaymentEnabled,
                    'isBankAccount' => $account->isBankAccount,
                    'isArchived' => $account->isArchived,
                    'id' => $company['id']
                );

            }
            $companyAccounts[] = $newAccountStatistic;
        }

        $this->smarty->assignByRef('companyAccounts', $companyAccounts);
        $this->smarty->assignByRef('custom_page_title', lang('Received accounts'));
    }

    private function getCountryCode($company)
    {
        $countryCode = 'DK'; //default set to Denmark in case the custom fields module is not installed.

        if (AngieApplication::isModuleLoaded('cust_fields')) { //if the custom fields module is installed proceed with retrieving the real country field.

            $values = CustFieldsModule::getAddOptionsValues($company, 'company');

            if (isset($values['1_Country']['value'])) { // only continue from here if the country has been set for this company
                $countries = new Countries();
                $countries = $countries->getCountries();
                $country = array_keys($countries, $values['1_Country']['value']);
                if (isset($country[0])) {
                    $countryCode = $country[0];
                }
            }
        } // if
        return $countryCode;
    }


    function synchronize_companies()
    {
        $response = BillysRequest::send('GET', '/user/organizations'); //request the organizations

        $billys_organizations = $response->body->organizations; //getting the organizations from the response body

        $local_unassociated_companies = BillysCompany::getAllUnassociatedCompanies(); //getting the local companies from our system

        $local_associated_companies = BillysCompany::getAllAssociatedCompanies();

        if (!empty($local_associated_companies)) {
            $associated_company_names = array_column($local_associated_companies->toArray(), 'id', 'name');
        } else {
            $associated_company_names = array();
        }

        $organizations = array();

        $operations = array(
            'added_to_billy' => 0,
            'added_to_local' => 0,
            'updated_local' => 0
        );


        foreach ($billys_organizations as $organization) {
            $organizations[$organization->name] = $organization; //using the company name as the organization key will make the next operations easier and probably faster

        }

        $user = Authentication::getLoggedUser();


        foreach ($local_unassociated_companies as $company) {

            if (!isset($organizations[$company['name']])) { //we probably don't duplicate names. Billy's Billing can create duplicate names but with different IDs.
                //In order to avoid that and have a one-to-one relation we check if the company name already exists in the Billy's Billing system

                if (($user->isOwner()) && ($company['id'] == $user->getCompanyId())) { //only add the company to Billy's Billings as an organization if the user is the owner
                    $response = BillysRequest::send('POST', '/organizations', array(
                        'organization' => array(
                            'name' => $company['name'],
                            'countryId' => 'DK'
                        )));

                    $organization = new BillysCompany($company['id']);
                    $organization->setOrganizationId($response->body->organizations[0]->id);
                    $organization->save();
                    $operations['added_to_billy']++;
                }

            } else {
                $organization = new BillysCompany($company['id']); //this else is necessary because there were some companies which were both stored locally and on the Billy's Billing system, but weren't related
                $organization->setOrganizationId($organizations[$company['name']]->id);
                $organization->save();
                unset($organizations[$company['name']]); //cleaning the processed fields so we know at the end if we need to add companies to the local system.
                $operations['updated_local']++;
            }

        }

        //if after the cleanup there are any values left in the response, add those companies to the local system
        $user = Authentication::getLoggedUser();
        $time = new DateTimeValue(time());

        foreach ($organizations as $name => $organization) {

            if (!isset($associated_company_names[$name])) {

                //creating the company in the local system

                $company = new Company();

                $company->setName($name);
                $company->setCreatedOn($time);
                $company->setCreatedBy($user);

                $company->save();

                //associating it with it's organization ID from Billy's Billing

                $company_id = $company->getId();
                $billys_company = new BillysCompany($company_id);
                $billys_company->setOrganizationId($organization->id);
                $billys_company->save();
                $operations['added_to_local']++;
            }
        }
        $this->smarty->assignByRef('operations', $operations);
    }


    /**
     * Blowfish decryption
     * @param $value
     * @param $key
     * @return string
     */
    private function encrypt($value, $key)
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
    private function decrypt($value, $key)
    {
        $iv = pack("H*", substr($value, 0, 16));
        $x = pack("H*", substr($value, 16));
        $decrypted_value = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $x, MCRYPT_MODE_CBC, $iv);
        return trim($decrypted_value);


    }

    private function getEncryptionKey()
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
    private function setEncryptionKey($key)
    {
        $this->encryption_key = $key;
        file_put_contents('key', $key);
    }


}