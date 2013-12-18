<?php
header('Content-type: text/html; charset=utf-8');

include dirname(__FILE__) . '/vendor/autoload.php';

include dirname(__FILE__) . '/Translate.php';

$translate = Translate::getInstance();

echo '<html><head>';
date_default_timezone_set('America/Chicago');
$debugbar = new DebugBar\StandardDebugBar();
$debugbarRenderer = $debugbar->getJavascriptRenderer();
$debugbarRenderer->setBaseUrl('vendor/maximebf/debugbar/src/DebugBar/Resources/');
echo $debugbarRenderer->renderHead();
echo '</head><body>';

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

print "<pre>";
$strings = array(
    $translate->gettext_noop("Configuration"),
    $translate->gettext_noop("Control Panel"),
    $translate->gettext_noop("Could not install autorun file. Please try again."),
    $translate->gettext_noop("Select a file or a folder"),
    $translate->gettext_noop("When ejecting the drive, close the apps that are locking it"),
    $translate->gettext_noop("You do not have sufficient privileges for this operation."),
);
$debugbar['time']->startMeasure('Translate', '6*gettext()');
foreach ($strings as $s) {
    $t=$translate->gettext($s);
    echo "<em>$s</em><br /><pre>      $t</pre><br /><hr />";
}
$debugbar['time']->stopMeasure('Translate');
print "</pre>\n";

echo '<br /><hr><a href="index.php">Plurals</a> | <a href="context.php">Context</a>'
. ' | <a href="example2.php">RMC Files</a> | <a href="example3.php">Misc</a><br />';

echo $debugbarRenderer->render();
echo '</body></html>';
