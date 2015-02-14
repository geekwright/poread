<?php
include 'header.php';

$translate = Translate::getInstance();

function gettext_noop($value)
{
    global $translate;
    return $translate->gettext_noop($value);
}

$locale = (isset($_GET['lang']))? $_GET['lang'] : 'en';

echo '<h1>Misc Example</h1>';
echo '<p>Translate example using a chinese po file. Translation is based on GNU gettext "po" files.</p>';

$supported_locales = array('en', 'zh');
print '<p><strong>Select Locale:</strong> ';
foreach ($supported_locales as $l) {
    print "[<a href=\"?lang=$l\">$l</a>] ";
}
print "</p>\n";

$debugbar['time']->startMeasure('PoRead', 'Translate::textdomain()');

$translate->textdomain('test', $locale);

$debugbar['time']->stopMeasure('PoRead');

for ($number=6; $number>=0; $number--) {
    print sprintf(
        $translate->ngettext("%d pig went to the market", "%d pigs went to the market", $number),
        $number
    );
    print "<br>";
}

echo _('Short tag');

$translate->pgettext('tool', 'Saw');
$translate->pgettext('observed', 'Saw');

print "<pre>";
$strings = array(
    $translate->gettext_noop('Configuration'),
    gettext_noop (<<<'EOT'
Control Panel
with a lot of options
EOT
),
    // This is a comment for the translator
    gettext_noop("Could not install autorun file. Please try again."),
    /*
    this comment should be ignored
    */
    gettext_noop("Select a file or a folder"),
    /* refering to floppy disk drive */
    gettext_noop("When ejecting the drive, close the apps that are locking it"),
    gettext_noop("You do not have sufficient privileges for this operation."),
);
$debugbar['time']->startMeasure('Translate', '6*gettext()');
foreach ($strings as $s) {
    $t=$translate->gettext($s);
    echo "<em>$s</em><br /><pre>      $t</pre><br /><hr />";
}
$debugbar['time']->stopMeasure('Translate');
print "</pre>\n";

echo $translate->ngettext("%d pig went to the market", "%d pigs went to the market", 2);

echo '<br /><hr><a href="index.php">Plurals</a> | <a href="context.php">Context</a>'
. ' | <a href="example2.php">RMC Files</a> | <a href="example3.php">Misc</a><br />';

include 'footer.php';
