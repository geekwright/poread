<?php
/**
 * generate a pot file for a given set of php and smarty template files
 *
 * Incomplete experiment
 */
use Symfony\Component\Finder\Finder;

include 'header.php';

echo '<pre>';
$dir = "/home/richard/sites/XoopsCore/htdocs/modules/publisher/";
$dir = __DIR__;
$finder = gatherSourceFiles($dir, '*.php');
foreach ($finder as $file) {
    // Print the absolute path
    print $file->getRealpath()."\n";
}

$finder = gatherSourceFiles($dir, '*.tpl');
foreach ($finder as $file) {
    print $file->getFilename()."\n";
    // Print the absolute path
    print $file->getRealpath()."\n";
    \Kint::dump($file->getContents());
}

echo '</pre>';

include 'footer.php';


/**
 * gatherSourceFiles
 *
 * @param string $dir
 * @param string $name
 * @return Finder
 */
function gatherSourceFiles($dir, $name)
{
    $finder = new Finder();
    try {
        $finder->files()->in($dir)->name($name)->notPath('cldr')->notPath('vendor')->notPath('cache')->notPath('templates_c');
    } catch (\Exception $e) {
        \Kint::dump($e);
    }
    return $finder;
}

/*
# SOME DESCRIPTIVE TITLE
# Copyright (C) YEAR Free Software Foundation, Inc.
# This file is distributed under the same license as the PACKAGE package.
# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: PACKAGE VERSION\n"
"POT-Creation-Date: 2008-02-06 16:25-0500\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"
"Language-Team: LANGUAGE <LL@li.org>\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=CHARSET\n"
"Content-Transfer-Encoding: ENCODING\n"

white-space
#  translator-comments
#. extracted-comments
#: reference…
#, flag…
#| msgid previous-untranslated-string
msgid untranslated-string
msgstr translated-string

white-space
#  translator-comments
#. extracted-comments
#: reference…
#, flag…
#| msgctxt previous-context
#| msgid previous-untranslated-string
msgctxt context
msgid untranslated-string
msgstr translated-string

white-space
#  translator-comments
#. extracted-comments
#: reference…
#, flag…
#| msgid previous-untranslated-string-singular
#| msgid_plural previous-untranslated-string-plural
msgid untranslated-string-singular
msgid_plural untranslated-string-plural
msgstr[0] translated-string-case-0
...
msgstr[N] translated-string-case-n

*/
