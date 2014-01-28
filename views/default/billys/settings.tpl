<style type="text/css" ></style>
{title}Billys Billing Settings{/title}
<div id="billys_settings">

    <div class="content_stack_wrapper">
        <div class="content_stack_element">
            <div class="content_stack_element_body">
                <div class="billys_options">
                {if $config.billys_user_id}
                    <p>
                        <label class="main_label">
                            Billys Billing user ID:
                            <b>
                                {$config.billys_user_id}
                            </b>
                        </label>
                    </p>
                    {if $see_job_types}
                        <p>
                            <a class="fright button_like" href="{Router::assemble('billys_see_job_types')}">View job types</a>
                        </p>
                    {/if}

                    {if $sync_job_types}
                        <p>
                            <a class="fleft button_like" href="{Router::assemble('billys_synchronize_job_types')}">Synchronize job types</a>
                        </p>
                    {/if}

                    <div class="fclear"></div>

                    <p>
                        <a class="fleft button_like" href="{Router::assemble('billys_synchronize_contacts')}">Synchronize contacts</a>
                    </p>

                    <p>
                        <a class="fright button_like" href="{Router::assemble('billys_see_contacts')}">See contacts</a>
                    </p>

                    <div class="fclear"><br/></div>

                    {if $see_accounts}
                        <p>
                            <a class="button_like" href="{Router::assemble('billys_see_accounts')}">Change your organization's account used for sending invoices</a>
                        </p>
                    {/if}

                    <a class="button_like" href="{Router::assemble('billys_match_organization')}">Setup organization</a>
                {/if}

                </div>

                {form action=Router::assemble('billys_settings')}
                {wrap field=billys_billing_api_key}
                {text_field name='settings[billys_billing_api_key]' value=$config.billys_billing_api_key label="API Key"}
                {/wrap}
                {wrap field=billys_billing_secret}
                {text_field name='settings[billys_billing_secret]' value=$config.billys_billing_secret label="Secret"}
                {/wrap}
                {wrap field=billys_billing_email}
                {text_field name='settings[billys_billing_email]' value=$config.billys_billing_email label="Email"}
                {/wrap}
                {wrap field=billys_billing_password}
                {password_field name='settings[billys_billing_password]' value=$config.billys_billing_password label="Password"}
                {/wrap}
                {wrap_buttons}
                {submit}Save Changes{/submit}
                {/wrap_buttons}
                {/form}

            </div>
        </div>
    </div>
</div>