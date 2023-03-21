<?php

$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return [
    "Composer\\" => $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "composer", "composer", "src", "Composer"]),
    "Composer\\XdebugHandler\\" => $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "composer", "xdebug-handler", "src"]),
    //--dev
    "Composer\\Pcre\\" =>
    [
        $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "composer", "pcre", "src"]),
        $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "composer", "pcre", "tests"]) //--dev
    ],
    "Psr\\Log\\" => $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "psr", "log", "src"]),

    //Inclusion phase de tests.
    "Composer\\XdebugHandler\\Tests\\" => $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "composer", "xdebug-handler", "tests"]),
];
?>