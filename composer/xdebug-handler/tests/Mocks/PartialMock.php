<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */


namespace Composer\XdebugHandler\Tests\Mocks;

/**
 * PartialMock fournit sa propre méthode de redémarrage qui définit simplement la propriété restarted sur true, 
 * plutôt que de simuler un redémarrage.
 * 
 * Il peut être utilisé pour tester l'état du processus parent d'origine.
 */
class PartialMock extends CoreMock
{
    /**
     * @var string[]
     */
    protected array $command;

    /**
     * @return string[]
     */
    public function getCommand(): array
    {
        return $this->command;
    }

    public function getTmpIni(): ?string
    {
        return $this->tmpIni;
    }

    /**
     * @inheritDoc
     */
    protected function restart(array $command): void
    {
        $this->command = $command;
        $this->restarted = true;
    }
}