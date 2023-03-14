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

/**
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 *
 * @phpstan-import-type restartData from PhpConfig
 */
class XdebugHandler
{
    const SUFFIX_ALLOW = '_ALLOW_XDEBUG';

    const SUFFIX_INI = '_ORIGINAL_INI';

    const RESTART_ID = 'INTERNAL';

    const _RESTART_SETTINGS = 'XDEBUG_HANDLER_SETTINGS';

    const DEBUG = 'XDEBUG_HANDLER_DEBUG';

    /**
     * @var string|null
     */
    protected ?string $tmpIni;

    /**
     * @var bool
     */
    private static bool $inRestart;

    /**
     * @var string
     */
    private static string $name;

    /**
     * @var string|null
     */
    private static ?string $skipped;

    /**
     * @var bool
     */
    private static bool $xdebugActive;

    /**
     * @var string|null
     */
    private static ?string $xdebugMode;

    /**
     * @var string|null
     */
    private static ?string $xdebugVersion;

    /**
     * @var bool
     */
    private bool $cli;

    /**
     * @var string|null
     */
    private ?string $debug;

    /**
     * @var string
     */
    private string $envAllowDebug;

    /**
     * @var string
     */
    private string $envOriginalIni;

    /**
     * @var bool
     */
    private bool $persistant;

    /**
     * @var string|null
     */
    private ?string $script;

    /**
     * @var Status
     */
    private Status $statusWriter;

}