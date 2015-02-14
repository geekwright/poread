<?php
include 'header.php';

$pattern = '/((^(#|#.|#;|#,|#\||msgid|msgid_plural|msgctxt|msgstr)\s)|(^msgstr\[)([0-9]+)\]\s|(^"))(.+)/';
$pattern = '/(^(#|#.|#;|#,|#\||msgid|msgid_plural|msgctxt|msgstr|msgstr\[([0-9]+)\])\s|(^["#]))(.+)/';

/**
 * This is an incredibly ugly regex pattern that breaks a line of a po file into
 * pieces that can be analyzed and acted upon.
 *
 * The matches array in preg_match will break out like this:
 *  [0] full string
 *  [1] mostly useless broad match of initial token, including trailing space
 *  [2] bare token, or full msgstr[n] clause
 *  [3] 'n' of a msgstr[n] line
 *  [4] '"' if a data line
 *  [5] remaining line
 *  [6] a bare or malformed comment
 */
$pattern = '/(^(#|#.|#;|#,|#\||msgid|msgid_plural|msgctxt|msgstr|msgstr\[([0-9]+)\])\s|(^"))(.+)|(^#.*)/';

$file = 'locale/rmc/es.po';
$file = 'locale/pigs/fr.po';
$file = 'test.pot';
$source = file_get_contents($file);
$source_lines = explode("\n", $source);

//echo "<pre>";

$wsBreak = false;
$inHeader = true;
$entry = new PoHeader;
$headerEntry = null;
$allEntries = array();
foreach ($source_lines as $s) {
    $result = preg_match($pattern, $s, $matches);
    if (!$result) {
        $lastKey = '';
        if ($s=='' || ctype_space($s)) {
            if ($inHeader) {
                $headerEntry = $entry;
                $entry = null;
                //echo "end of header\n";
                $inHeader = false;
            }
            if (!$wsBreak) {
                if (!is_null($entry)) {
                    $allEntries[] = $entry;
                }
                $entry = null;
                //echo "----------------- store and start new item\n";
                $wsBreak=true;
            }
        } else {
            $wsBreak=false;
            echo "UNRECOGNIZED {$s}\n";
            \Kint::dump($s);
        }
    } else {
        if (is_null($entry)) {
            $entry = new PoEntry;
        }
        $wsBreak=false;
        $currentKey = $matches[2];  // will be used to set last key
        switch ($matches[2]) {
            case PoTokens::TRANSLATOR_COMMENTS:
                //echo "comment: {$matches[5]}\n";
                $entry->add(PoTokens::TRANSLATOR_COMMENTS, $matches[5]);
                break;
            case PoTokens::EXTRACTED_COMMENTS:
                //echo "source comment: {$matches[5]}\n";
                $entry->add(PoTokens::EXTRACTED_COMMENTS, $matches[5]);
                break;
            case PoTokens::REFERENCE:
                //echo "reference: {$matches[5]}\n";
                $entry->add(PoTokens::REFERENCE, $matches[5]);
                break;
            case PoTokens::FLAG:
                $entry->add(PoTokens::FLAG, $matches[5]);
                //echo "flag: {$matches[5]}\n";
                break;
            case PoTokens::OBSOLETE:
                $entry->add(PoTokens::OBSOLETE, $matches[5]);
                //echo "obsolete: {$matches[5]}\n";
                break;
            case PoTokens::CONTEXT:
                $entry->addQuoted(PoTokens::CONTEXT, $matches[5]);
                //echo "context: {$matches[5]}\n";
                break;
            case PoTokens::MESSAGE:
                $entry->addQuoted(PoTokens::MESSAGE, $matches[5]);
                //echo "message: {$matches[5]}\n";
                break;
            case PoTokens::PLURAL:
                $entry->addQuoted(PoTokens::PLURAL, $matches[5]);
                //echo "plural message: {$matches[5]}\n";
                break;
            case PoTokens::TRANSLATED:
                $entry->addQuoted(PoTokens::TRANSLATED, $matches[5]);
                //echo "translated message: {$matches[5]}\n";
                break;
            case PoTokens::PREVIOUS:
                $entry->add(PoTokens::PREVIOUS, $matches[5]);
                //echo "previously: {$matches[5]}\n";
                break;
            default:
                if ($matches[4]==PoTokens::CONTINUED_DATA) {
                    $currentKey = $lastKey; // keep the previous key
                    if ($currentKey==PoTokens::TRANSLATED_PLURAL) {
                        $entry->addQuotedAtPosition(PoTokens::TRANSLATED, '"' . $matches[5], $currentPlural);
                    } else {
                        $entry->addQuoted($currentKey, '"' . $matches[5]);
                    }
                    //echo "dataline \"{$matches[5]}\n";
                } elseif (substr($matches[2], 0, 7)==PoTokens::TRANSLATED_PLURAL) {
                    $currentKey = PoTokens::TRANSLATED_PLURAL;
                    $currentPlural = $matches[3];
                    $entry->addQuotedAtPosition(PoTokens::TRANSLATED, $matches[5], $currentPlural);
                    // echo "translated plural {$matches[3]} {$matches[5]}\n";
                } elseif (isset($matches[6][0]) && $matches[6][0]==PoTokens::TRANSLATOR_COMMENTS) {
                    $value = substr($matches[6], 1);
                    $value = empty($value) ? '' : $value;
                    $entry->add(PoTokens::TRANSLATOR_COMMENTS, $value);
                    //echo "bare comment: {$matches[6]}\n";
                } else {
                    //echo "UNRECOGNIZED {$s}\n";
                    //echo "<pre>";
                    \Kint::dump($s, $matches);
                    //echo "</pre>";
                }
                break;
        }
        $lastKey = $currentKey;
    }
}
if (!is_null($entry)) {
    $allEntries[] = $entry;
    $entry = null;
}

\Kint::dump($headerEntry, $allEntries);

echo "<pre>";
//echo $headerEntry->getHeader('plural-forms') . "\n\n";
echo $headerEntry->dumpEntry();
foreach ($allEntries as $entry) {
    echo $entry->dumpEntry();
}
echo "</pre>";

include 'footer.php';
