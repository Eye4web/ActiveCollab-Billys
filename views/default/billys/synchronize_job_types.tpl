{title}Synchronized job types to products{/title}
<div id="billys_settings">
    <div class="content_stack_wrapper">
        <div class="content_stack_element">
            <div class="content_stack_element_body">
                <a href="{Router::assemble('billys_settings')}">Back</a>
                <table class="common auto list_items admin_list">
                    <thead>

                    </thead>
                    <tbody>
                    {foreach from=$projectsJobTypes item=projectJobTypes}
                        <tr>
                            <td>
                                {$projectJobTypes.projectName}
                            </td>
                            <td>
                                <table>
                                    <thead>

                                    </thead>
                                    <tbody>
                                    {foreach from=$projectJobTypes.jobTypes item=jobType}
                                        <tr>
                                            <td>
                                                {$jobType.name}
                                            </td>
                                            <td>
                                                {$jobType.price}
                                            </td>
                                            <td>
                                                {$jobType.currency}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <a href="{Router::assemble('billys_settings')}">Back</a>
            </div>
        </div>
    </div>
</div>
