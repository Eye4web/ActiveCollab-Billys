{title}Organizations Synchronization...{/title}
<div id="billys_settings">

    <div class="content_stack_wrapper">
        <div class="content_stack_element">
            <div class="content_stack_element_body">
                <h3>Operations</h3>

                <p>Companies added to Billy's Billing: {$operations.added_to_billy}</p>

                <p>Companies added to local system: {$operations.added_to_local}</p>

                <p>Companies updated locally: {$operations.updated_local}</p>

                <p><br/>  <a href="{Router::assemble('billys_settings')}">Back</a></p>
            </div>
        </div>
    </div>
</div>