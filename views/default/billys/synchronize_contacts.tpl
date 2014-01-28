{title}{$cpage_title}{/title}
<div id="billys_settings">
    <div class="content_stack_wrapper">
        <div class="content_stack_element">
            <div class="content_stack_element_body">
                <p><br/> <a href="{Router::assemble('billys_settings')}">Back</a><br/></p>
                <table>
                    <thead>
                    <tr>
                        <th>Company</th>
                        <th>Id</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$contacts item=contact}
                        <tr>
                            <td>
                                {$contact.name}
                            </td>
                            <td>
                                {$contact.contactId}
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
