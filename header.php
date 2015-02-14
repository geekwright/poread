<?php
header('Content-type: text/html; charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/Translate.php';

require_once __DIR__ . '/class/PoEntry.php';
require_once __DIR__ . '/class/PoHeader.php';
require_once __DIR__ . '/class/PoTokens.php';

echo '<html><head>';
date_default_timezone_set('America/Chicago');
$debugbar = new \DebugBar\StandardDebugBar();
$debugbarRenderer = $debugbar->getJavascriptRenderer();
$debugbarRenderer->setBaseUrl('vendor/maximebf/debugbar/src/DebugBar/Resources/');
echo $debugbarRenderer->renderHead();

echo '</head><body>';
