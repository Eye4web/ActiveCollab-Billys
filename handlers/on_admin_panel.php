<?php

/**
 * on_admin_panel event handler
 *
 * @package angie.frameworks.invoicing
 * @subpackage handlers
 */

/**
 * Handle on_admin_panel event
 *
 * @param AdminPanel $admin_panel
 */
function billys_handle_on_admin_panel(AdminPanel &$admin_panel)
{
    $admin_panel->addToGeneral('billys_settings', lang('Billy\'s Billing settings'), Router::assemble('billys_settings'), AngieApplication::getImageUrl('module.png', BILLYS_MODULE));
} // invoicing_handle_on_admin_panel