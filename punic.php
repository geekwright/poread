<?php
use \Punic\Data;
use \Punic\Language;
use \Punic\Misc;

include 'header.php';

//$translate = Translate::getInstance();

Data::setDefaultLocale('de_DE');

$langs = Misc::getBrowserLocales();//Language::getAll();
$locales = Data::getAvailableLocales();
$ownLangs = array();

foreach ($locales as $l) {
    $ownLangs[$l] = Language::getName($l, $l);
}

\Kint::dump($langs, $locales, $ownLangs);

include 'footer.php';
