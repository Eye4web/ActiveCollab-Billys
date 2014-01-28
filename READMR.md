The module uses API v2 which is not yet officially released and some functionality could not be tested successfully - like creating payments.
Create the folder "/custom/models/billys" and clone these files in there.
Install the module.
Go to Administration and find the "Billy's Billing settings" icon in the General section.
Put in the email and password used for Billy's Billing authentication. Ignore the API Key and Secret because at the moment
Billy's Billing API doesn't use them.
Save and then Setup organization, select billings account and synchronize contacts and then job types.
Job types will be the products in the Billys Billing system.
When issuing an invoice, the invoice will also be created in Billy's Billing.