<?php
include 'header.php';

//$translate = Translate::getInstance();


$basedir = dirname(__FILE__);

$smarty = new Smarty();

$smarty->setTemplateDir($basedir . '/smarty/templates');
$smarty->setCompileDir($basedir . '/smarty/templates_c');
$smarty->setCacheDir($basedir . '/smarty/cache');
$smarty->setConfigDir($basedir . '/smarty/configs');

$smarty->addPluginsDir($basedir . '/smarty/plugins');

$smarty->left_delimiter = '<{';
$smarty->right_delimiter = '}>';

$smarty->assign('name', 'Fred');

$template = 'index.tpl';
$tpl = $smarty->createTemplate($template);

// get tags
$tags = $smarty->getTags($tpl);

\Kint::dump($tags);
$translate_funcs = array('gettext', '_');

$msg_types = array('msgctxt', 'msgid', 'msgid_plural');


$tokenCount = count($tokens);
$gtRefs = array();
foreach ($tags as $tag) {
    if (in_array($tag[0], $translate_funcs)) {
        $args = array();
        foreach ($tag[1] as $temp) {
            foreach ($temp as $key => $value) {
                if (in_array($key, $msg_types)) {
                    $args[$key] = $value;
                }
            }
        }
        $gtRefs[] = array_merge(
            array('file' => $template, 'function' => $tag[0], 'args' => $tag[1]),
            $args
        );
    }
}
\Kint::dump($gtRefs);
//exit;
//$debugbar["messages"]->addMessage($tags);
//$smarty->assign('vdump', print_r($tags, true));

$dbb_tail = $debugbarRenderer->render();
$smarty->assign('dbb1', $dbb_head);
$smarty->assign('dbb2', $dbb_tail);

$smarty->display('index.tpl');

include 'footer.php';
