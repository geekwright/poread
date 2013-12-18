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

echo '<h1>Context Example</h1>';
echo '<p>Translate strings using context.'
    . ' (These need the attention of a real translator, but it should convey the concept.)'
    . ' Identical strings may translate differently, especially GUI elements and menu items.'
    . ' The <em>msgctxt</em> element gives us a way to differentiate these.</p>';
$supported_locales = array('en_US', 'ru_RU');
print '<p><strong>Select Locale:</strong> ';
foreach ($supported_locales as $l) {
    print "[<a href=\"?lang=$l\">$l</a>] ";
}
print "</p>\n";

$debugbar['time']->startMeasure('PoRead', 'Translate::textdomain()');

$translate->textdomain('context', $locale);

$debugbar['time']->stopMeasure('PoRead');

$debugbar['time']->startMeasure('Translate', '6*pgettext()');

$t=$translate->pgettext('direction|south', 'S');
echo "<em>S</em> context <strong>direction|south</strong><br /><pre>      $t</pre><br /><hr />";

$t=$translate->pgettext('time|seconds', 'S');
echo "<em>S</em> context <strong>time|seconds</strong><br /><pre>      $t</pre><br /><hr />";

$t=$translate->pgettext('size|small', 'S');
echo "<em>S</em> context <strong>size|small</strong><br /><pre>      $t</pre><br /><hr />";

$t=$translate->pgettext('direction|north', 'N');
echo "<em>N</em> context <strong>direction|north</strong><br /><pre>      $t</pre><br /><hr />";

$t=$translate->pgettext('nitrogen', 'N');
echo "<em>N</em> context <strong>nitrogen</strong><br /><pre>      $t</pre><br /><hr />";

$t=$translate->pgettext('no', 'N');
echo "<em>N</em> context <strong>no</strong><br /><pre>      $t</pre><br /><hr />";

$debugbar['time']->stopMeasure('Translate');

echo '<br /><hr><a href="index.php">Plurals</a> | <a href="context.php">Context</a>'
. ' | <a href="example2.php">RMC Files</a> | <a href="example3.php">Misc</a><br />';

echo $debugbarRenderer->render();
echo '</body></html>';
