<?php declare(strict_types=1);

$vendorBin = implode(DIRECTORY_SEPARATOR, ['', 'home', 'julie', 'vendor', 'bin']);

//Voir si nous avons besoin de simple-phpunit
$path = realpath($vendorBin . DIRECTORY_SEPARATOR . 'simple-phpunit');

if ($path !== false) {
    //simple-phpunit met a jour le lien symbolique/fonction <.phpunit>
    $phpunit = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path);
    passthru($phpunit . ' install');

    $autoloader = $vendorBin . implode(DIRECTORY_SEPARATOR, ['', '.phpunit', 'phpunit', 'vendor', 'autoload.php']);
    if (!file_exists($autoloader)) {
        echo 'Impossible de lancer PHPStan: simple-phpunit n\'a pus installé PHPUnit.' . PHP_EOL;
        exit(1);
    }
    include($autoloader);
    return;
}

if (realpath($vendorBin . DIRECTORY_SEPARATOR . 'phpunit') === false) {
    echo 'Impossible de lancer PHPStan: PHPUnit, n\'est pas installé.' . PHP_EOL;
    exit(1);
}