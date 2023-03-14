<?php

$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return [
    "Composer\\" => $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "composer", "composer", "src", "Composer"]),
    "Composer\\XdebugHandler\\" => $vendorDir . implode(DIRECTORY_SEPARATOR, ["", "composer", "xdebug-handler", "src"]),
];
?>