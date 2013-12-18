<?php
/**
 * Dump tokens for a PHP file. Looking for gettext functions
 *
 * Incomplete experiment
 */

$file = 'index.php';
$source = file_get_contents($file);
$source_lines = explode("\n", $source);
//var_dump($source);
$tokens = token_get_all($source);
//var_dump($tokens);

$translate_funcs = array('gettext', 'ngettext', 'pgettext');

foreach ($tokens as $token) {
    //var_dump($token);
    //if (is_string($token)) {
        // simple 1-character token are strings
        //echo '<pre>' . $token . "\n</pre>";
    if (is_array($token) && $token[0] == T_STRING && in_array($token[1], $translate_funcs)) {
        // token array
        list($id, $text, $line) = $token;
        $tname = token_name($id);
        echo '<pre>' . $source_lines[$line-1] . "\n</pre>";
        echo '<pre>'.$file .':'.$line . '  ' . $tname . '  ' . htmlspecialchars($text) . "\n</pre>";
    }
}
