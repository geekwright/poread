<?php
/**
 * Dump tokens for a PHP file. Looking for gettext functions
 *
 * Incomplete experiment
 */
include 'header.php';

//$file = 'index.php';
$file = 'tokentest.php';
$source = file_get_contents($file);
$source_lines = explode("\n", $source);
//var_dump($source);
$tokens = token_get_all($source);

$debugbar['messages']->debug($tokens);

$gettext_funcs = array('gettext', 'gettext_noop', '_');
$pgettext_funcs = array('pgettext');
$ngettext_funcs = array('ngettext');

$translate_funcs = array_merge($gettext_funcs, $pgettext_funcs, $ngettext_funcs);
//\Kint::dump($gettext_funcs, $pgettext_funcs, $ngettext_funcs, $translate_funcs);
$commentText=null;
$commentLine=(-10);
$tokenCount = count($tokens);
$gtRefs = array();
$i = 0;
while ($i<$tokenCount) {
//foreach ($tokens as $token) {
    $token = $tokens[$i];
    //var_dump($token);
    //if (is_string($token)) {
    //    // simple 1-character token are strings
    //    echo '<pre>' . $token . "\n</pre>";
    //} else
    if (is_array($token) && $token[0] == T_STRING && in_array($token[1], $translate_funcs)) {
        $gtt = array();
        // token array
        list($id, $text, $line) = $token;
        $tname = token_name($id);
        $gtt['source']=$source_lines[$line-1];
        //echo "<pre>start: --------------------\n</pre>";
        $gtt['ref']=$file . ':' . $line;
        $gtt['line']=$line;
        $gtt['function']=$text;
        $gtt['args'] = array();
        //echo '<pre>*'.$file .':'.$line . '  ' . $tname . '  ' . htmlspecialchars($text) . "\n</pre>";
        $la = 1;
        while (is_array($tokens[$i + $la]) &&  $tokens[$i + $la][0] == T_WHITESPACE) {
            $la++;
        }
        if ($tokens[$i + $la] == '(') {
            while ((')' != $token=$tokens[$i + $la]) && ($la < 10)) {
                if (is_array($token) && (
                    $token[0] == T_CONSTANT_ENCAPSED_STRING
                    || $token[0] == T_ENCAPSED_AND_WHITESPACE
                )) {
                    list($id, $text, $line) = $token;
                    $gtt['args'][]=$text;
                }
                $la++;
            }
            if (count($gtt['args'])) {
                if (in_array($gtt['function'], $gettext_funcs)) {
                    $gtt['msgid'] = escapeForPo($gtt['args'][0]);
                } elseif (in_array($gtt['function'], $pgettext_funcs)) {
                    $gtt['msgctxt'] = escapeForPo($gtt['args'][0]);
                    $gtt['msgid'] = escapeForPo($gtt['args'][1]);
                } elseif (in_array($gtt['function'], $ngettext_funcs)) {
                    $gtt['msgid'] = escapeForPo($gtt['args'][0]);
                    $gtt['msgid_plural'] = escapeForPo($gtt['args'][1]);
                }
                if ($gtt['line']==($commentLine+1)) {
                    $gtt['comment'] = $commentText;
                }
                $gtRefs[]=$gtt;
            }
        }
    } elseif (is_array($token) && $token[0] == T_COMMENT) {
        list($id, $commentText, $commentLine) = $token;
    }
    //else {
    //    list($id, $text, $line) = $token;
    //    $tname = token_name($id);
    //    echo '<pre>'.$tname.' : '. htmlspecialchars($text) . '</pre>';
    //}
    $i++;

}

$po = array();
foreach ($gtRefs as $gtt) {
    $key = '';
    if (isset($gtt['msgctxt'])) {
        $key .= $gtt['msgctxt'] . '|';
    }
    $key .= $gtt['msgid'];
    if (isset($gtt['msgid_plural'])) {
        $key .= '|' . $gtt['msgid_plural'];
    }

    if (!isset($po[$key])) {
        $po[$key] = new PoEntry();
    }
    $entry = $po[$key];
    $entry->set(PoTokens::MESSAGE, $gtt['msgid']);
    if (isset($gtt['msgctxt'])) {
        $entry->set(PoTokens::CONTEXT, $gtt['msgctxt']);
    }
    if (isset($gtt['msgid_plural'])) {
        $entry->set(PoTokens::PLURAL, $gtt['msgid_plural']);
    }
    if (isset($gtt['ref'])) {
        $entry->add(PoTokens::REFERENCE, $gtt['ref']);
    }
    if (isset($gtt['comment'])) {
        $entry->add(PoTokens::EXTRACTED_COMMENTS, stripComment($gtt['comment']));
    }
}

\Kint::dump($gtRefs, $po);

echo '<pre>' . dumpPot($po) . '</pre>';

include 'footer.php';

/**
 * escapeForPo prepare a string from tokenized output for use in a po file.
 * Remove any surrounding quotes, escape control characters and double qoutes
 * @param string $string
 * @return string
 */
function escapeForPo($string)
{
    if ($string[0]=='"' || $string[0]=="'") {
        $string = substr($string, 1, -1);
    }
    $string = stripcslashes($string);
    return addcslashes($string, "\0..\37\"");
}

/**
 * stripComment remove comment tags from string
 * @param string $string
 * @return string
 */
function stripComment($string)
{
    return trim(str_replace(array('//','/*','*/'), '', $string));
}

/**
 * dumpPot
 * @param array $po internal normalized po representation
 * @return string Output for POT file
 */
function dumpPot($po)
{
    $creationDate = date('Y-m-d H:iO');
    $header =<<<EOT
# SOME DESCRIPTIVE TITLE
# Copyright (C) YEAR HOLDER
# This file is distributed under the same license as the PACKAGE package.
# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: PACKAGE VERSION\\n"
"POT-Creation-Date: $creationDate\\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"
"Language-Team: LANGUAGE <LL@li.org>\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\n"
"X-Generator: PRODUCTPLUG\\n"
EOT;

    $output = '';

    $output .= $header . "\n\n";
    foreach ($po as $entry) {
        $output .= $entry->dumpEntry();
    }
    $output .= "\n";

    return $output;
}
