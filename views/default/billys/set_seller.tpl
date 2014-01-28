{title}{$custom_page_title}{/title}
<div id="billys_settings">
    <div class="content_stack_wrapper">
        <div class="content_stack_element">
            <div class="content_stack_element_body">
                <p>
                    Name: {$accountDetails.name}
                </p>

                <p>
                    Description: {$accountDetails.description}
                </p>

                <p>
                    <br/>
                    <a href="{Router::assemble('billys_settings')}">Back</a>
                    <br/>
                </p>

            </div>
        </div>
    </div>
</div>