<?php

/**
 * Invoicing module on_object_deleted event handler
 *
 * @package activeCollab.modules.invoicing
 * @subpackage handlers
 */

/**
 * on_object_deleted handler implemenation
 *
 * @param Object $object
 * @return null
 */
function billys_handle_on_object_deleted($object)
{
    if ($object instanceof Invoice) {

    }
} // invoicing_handle_on_object_deleted

?>