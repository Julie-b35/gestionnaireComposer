<?php declare(strict_types=1);

/*
 * Ceci est fait partie de Composer
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use \Composer\Autoload\ClassLoader;

function includeIfExists(string $file): ?ClassLoader
{
    return file_exists($file) ? include($file) : null;
}

$loaderFiles = [
    ['', '..', 'vendor', 'autoload.php'],
    ['', '..', '..', '..', 'autoload.php']
];
$loader = null;
foreach ($loaderFiles as $loaderFile) {
    $loader = includeIfExists(__DIR__ . implode(DIRECTORY_SEPARATOR, $loaderFile));
    if (!is_null($loader))
        break;
}
if (is_null($loader)) {
    echo "Vous devez définir un projet avec 'composer install'" . PHP_EOL .
        "Voir https://getcomposer.org/download/ pour plus d'information sur l'installation de Composer." . PHP_EOL;
    exit(1);
}

return $loader;
?>