<?php
header('Content-type: text/html; charset=utf-8');

include dirname(__FILE__) . '/vendor/autoload.php';

include dirname(__FILE__) . '/Translate.php';

echo '<html><head>';
date_default_timezone_set('America/Chicago');
$debugbar = new \DebugBar\StandardDebugBar();
$debugbarRenderer = $debugbar->getJavascriptRenderer();
$debugbarRenderer->setBaseUrl('vendor/maximebf/debugbar/src/DebugBar/Resources/');
echo $debugbarRenderer->renderHead();

$translate = Translate::getInstance();

echo '</head><body>';

$locale = (isset($_GET['lang']))? $_GET['lang'] : 'en_US';

echo '<h1>Pigs Example</h1>';
echo '<p>Translate example showing plural handling. Translation is based on GNU gettext "po" files.</p>';

$supported_locales = array('en_US', 'sr_CS', 'de_CH');
print '<p><strong>Select Locale:</strong> ';
foreach ($supported_locales as $l) {
    print "[<a href=\"?lang=$l\">$l</a>] ";
}
print "</p>\n";

$debugbar['time']->startMeasure('PoRead', 'Translate::textdomain()');

$translate->textdomain('pigs', $locale);

$debugbar['time']->stopMeasure('PoRead');

print "<pre>";
$debugbar['time']->startMeasure('Translate', '1*gettext() 6*ngettext()');
print $translate->gettext("This is how the story goes.\n\n");
for ($number=6; $number>=0; $number--) {
    print sprintf(
        $translate->ngettext("%d pig went to the market\n", "%d pigs went to the market\n", $number),
        $number
    );
}
$debugbar['time']->stopMeasure('Translate');
print "</pre>\n";

echo '<br /><hr><a href="index.php">Plurals</a> | <a href="context.php">Context</a>'
. ' | <a href="example2.php">RMC Files</a> | <a href="example3.php">Misc</a><br />';

echo $debugbarRenderer->render();
echo '</body></html>';
