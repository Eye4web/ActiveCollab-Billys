{title}{$page_title}{/title}
<div id="billys_settings">

    <div class="content_stack_wrapper">
        <div class="content_stack_element">
            <div class="content_stack_element_body">

                {if $organizations}
                    <p>Select the organization that will be managed with the credentials you have provided</p>
                    {form action=Router::assemble('billys_match_organization')}
                        <table class="common_table common">
                            <tbody>
                            {foreach from=$organizations item=organization}
                                <tr>
                                    <td>
                                        {$organization.name}
                                    </td>
                                    <td>
                                        {submit class="organizations" name='organizationId' value=$organization.organizationId}Select{/submit}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    {/form}
                {/if}

                {if $accounts}
                    <p>
                        <b>Select the account used when receiving payments...</b>
                    </p>
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
                        {foreach from=$accounts item=account}
                            <tr>
                                <td>{$account.name}</td>
                                <td>{$account.description}</td>
                                <td>{$account.natureId}</td>
                                <td>{$account.isPaymentEnabled}</td>
                                <td>{$account.isBankAccount}</td>
                                <td>{$account.isArchived}</td>
                                <td><a href="{assemble route=billys_billing_set_seller account_id={$account.id}}">Set
                                        seller</a></td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                {/if}

            </div>
        </div>
    </div>
</div>