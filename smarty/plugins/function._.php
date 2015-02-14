<?php
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function._.php
 * Type:     function
 * Name:     _
 * Purpose:  translate a string
 * -------------------------------------------------------------
 */
function smarty_function__($params, Smarty_Internal_Template $template)
{
    //$translate = Translate::getInstance();

    if (empty($params)) {
        trigger_error("_: missing parameters");
        return;
    }

    $ret = isset($params['msgid']) ? $params['msgid'] : '';
    dump($params); // from symfony/var-dumper
    return $ret;
}
