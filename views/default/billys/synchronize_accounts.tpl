{title}{$custom_page_title}{/title}
<div id="billys_settings">
    <div class="content_stack_wrapper">
        <div class="content_stack_element">
            <div class="content_stack_element_body">
                <p><br/> <a href="{Router::assemble('billys_settings')}">Back</a><br/></p>
                <table>
                    <thead>
                    <tr>
                        <th>Company</th>
                        <th>Account</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$companyAccounts item=companyAccount}
                        <tr>
                            <td>
                                {$companyAccount.companyName}
                            </td>
                            <td>
                                <table>
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Nature ID</th>
                                        <th>Is payment enabled</th>
                                        <th>Is bank account</th>
                                        <th>Is archived</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach from=$companyAccount.accountDetails item=accountDetails}
                                        <tr>
                                            <td>{$accountDetails.name}</td>
                                            <td>{$accountDetails.description}</td>
                                            <td>{$accountDetails.natureId}</td>
                                            <td>{$accountDetails.isPaymentEnabled}</td>
                                            <td>{$accountDetails.isBankAccount}</td>
                                            <td>{$accountDetails.isArchived}</td>
                                            <td><a href="{assemble route=billys_billing_set_seller account_id={$accountDetails.id}}">Set seller</a></td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <p><br/> <a href="{Router::assemble('billys_settings')}">Back</a><br/></p>
            </div>
        </div>
    </div>
</div>
