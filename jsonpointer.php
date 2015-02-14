<?php
/**
 * use json-pointer to access cldr
 *
 * Incomplete experiment
 */
use Rs\Json\Pointer;
use Rs\Json\Pointer\InvalidJsonException;
use Rs\Json\Pointer\NonexistentValueReferencedException;

include 'header.php';

function dump($item)
{
    global $debugbar;
    $debugbar['messages']->debug($item);
}

function addException($e)
{
    global $debugbar;
    $debugbar['exceptions']->addException($e);
}

$file = 'cldr/26/main/zh/layout.json';
$source = file_get_contents($file);

try {
    $jsonPointer = new Pointer($source);
} catch (InvalidJsonException $e) {
    addException($e);
}

if ($jsonPointer) {
    try {
        $all = $jsonPointer->get("/main");
        dump($all);
        $orientation = $jsonPointer->get("/main/zh/layout");
        dump($orientation);
    } catch (NonexistentValueReferencedException $e) {
        addException($e);
    }
}

include 'footer.php';
