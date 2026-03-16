#!/usr/bin/php
<?php
require __DIR__. '/php-fhir/vendor/autoload.php';


$xsdPaths = glob(  __DIR__ . '/../xsd/*');

print_r($xsdPaths);

foreach ($xsdPaths as $xsdPath) {
    $namespace = basename($xsdPath);
    $generator = new \DCarbone\PHPFHIR\ClassGenerator\Generator($xsdPath, __DIR__ . '/../lib', $namespace);
    $generator->generate();
}
