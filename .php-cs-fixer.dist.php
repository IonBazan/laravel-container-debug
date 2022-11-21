<?php

return (new PhpCsFixer\Config)
    ->setRules(array(
        '@Symfony' => true,
        '@PHP70Migration' => true,
        'ordered_imports' => true,
    ))
    ->setRiskyAllowed(true)
    ->setFinder(PhpCsFixer\Finder::create()->in(['src', 'tests']))
    ;
