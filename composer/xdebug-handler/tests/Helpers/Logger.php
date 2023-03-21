<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\XdebugHandler\Tests\Helpers;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    /**
     * @var string[]|\Stringable[]
     */
    protected array $output = [];

    /**
     * @inheritDoc
     * @phpstan-param mixed[] $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->output[] = $message;
    }
    /**
     * @return string[]|\Stringable[]
     */
    public function getOutput(): array
    {
        return $this->output;
    }
}