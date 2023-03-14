<?php declare(strict_type=1);

/*
 * This file is part of composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\XdebugHandler;

use Psr\Log\LoggerInterface;

/**
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 * @internal
 */
class Status
{
    const ENV_RESTART = 'XDEBUG_HANDLER_RESTART';

    const CHECK = 'Check';

    const ERROR = 'Error';

    const INFO = 'Info';

    const NO_RESTART = 'No_Restart';

    const RESTART = 'Restart';

    const RESTARTING = 'Restarting';

    const RESTARTED = 'Restarted';

    /**
     * @var bool
     */
    private bool $debug;

    /**
     * @var string
     */
    private string $envAllowDebug;

    /**
     * @var string|null
     */
    private ?string $loaded;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var bool
     */
    private bool $modeOff;

    /**
     * @var float
     */
    private float $time;

}