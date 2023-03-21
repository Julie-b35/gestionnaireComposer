<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\XdebugHandler;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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
    private string $envAllowXdebug;

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

    /**
     * @param string    $envAllowXdebug  nom Préfixé de _ALLOW_XDEBUG
     * @param bool      $debug          Si la sortie de débogage est requise.
     */
    public function __construct(string $envAllowXdebug, bool $debug)
    {
        $start = getenv(self::ENV_RESTART);
        Process::setEnv(self::ENV_RESTART);
        $this->time = is_numeric($start) ? round((microtime(true) - $start) * 1000) : 0;

        $this->envAllowXdebug = $envAllowXdebug;
        $this->debug = $debug && defined('STDERR');
        $this->modeOff = false;
    }

    /**
     * Active la sortie du message d'état vers un enregistreur PSR3
     * 
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Appelle une méthode de gestionnaire pour signaler un message
     * 
     * @param string $op
     * @param string|null $data
     * 
     * @return void
     * @throws \InvalidArgumentException Si $op n'est pas connus.
     */
    public function report(string $op, ?string $data): void
    {
        if (isset($this->logger) || $this->debug) {
            $method = 'report' . $op;

            if (!method_exists($this, $method)) {
                throw new \InvalidArgumentException("gestionnaire op : " . $op . ", inconnus.");
            }
            $callable = [$this, $method];
            $params = $data !== null ? [$data] : [];

            if (is_callable($callable)) {
                call_user_func_array($callable, $params);
            }
        }
    }

    /**
     * Écris sur la sortie un message d'état.
     * 
     * @param string $text
     * @param string|null $level
     * @return void
     */
    private function output(string $text, ?string $level = null): void
    {
        if (isset($this->logger)) {
            $this->logger->log(!is_null($level) ? $level : LogLevel::DEBUG, $text);
        }

        if ($this->debug) {
            fwrite(STDERR, sprintf('xdebug-handler[%d] %s', getmypid(), $text . PHP_EOL));
        }
    }


    /**
     * Contrôle le message d'état
     * 
     * @param string $loaded
     * @return void
     * @phpstan-ignore-next-line
     */
    private function reportCheck(string $loaded): void
    {
        list($version, $mode) = explode('|', $loaded);

        if ($version !== '') {
            $this->loaded = '(' . $version . ') ' . ($mode !== '' ? 'xdebug.mode=' . $mode : '');
        }
        $this->modeOff = $mode === 'off';
        $this->output('Contrôle', $this->envAllowXdebug);
    }

    /**
     * Message d'état d'erreur
     * @phpstan-ignore-next-line
     * 
     * @param string $error
     * 
     * @return void
     */
    private function reportError(string $error): void
    {
        $this->output(sprintf('Erreur survenue pendant redémarrage (%s)', $error), LogLevel::WARNING);
    }

    /**
     * Émet un état d'info
     * @phpstan-ignore-next-line
     * @param string $info
     * @return void
     */
    private function reportInfo(string $info): void
    {
        $this->output($info);
    }

    /**
     * Émet l'état Pas de redémarrage
     * @phpstan-ignore-next-line
     * 
     * @return void
     */
    private function reportNoRestart(): void
    {
        var_dump($this->modeOff);
        trigger_error('Revenir à la fonction reportNoRestart()');
    }

    /**
     * Émet l'état redémarrage
     * @phpstan-ignore-next-line
     * @return void
     */
    private function reportRestart(): void
    {
        $this->output($this->getLoadedMessage());
        Process::setEnv(self::ENV_RESTART, (string) microtime(true));
    }

    /**
     * Émet l'état redémarré
     * @phpstan-ignore-next-line
     * 
     * @return void
     */
    private function reportRestarted(): void
    {
        $loaded = $this->getLoadedMessage();
        $text = sprintf('Processus redémarré en (%d ms). %s', $this->time, $loaded);
        $level = isset($this->loaded) ? LogLevel::WARNING : null;
        $this->output($text, $level);
    }

    /**
     * Émet l'état redémarrage en cours
     * @phpstan-ignore-next-line
     * 
     * @return void
     */
    private function reportRestarting(string $command): void
    {
        $txt = sprintf('Processus de redémarrage (%s)', $this->getEnvAllow());
        $this->output($txt);
        $txt = 'Lancement de la commande : ' . $command;
        $this->output($txt);
    }

    /**
     * Renvoie la variable d'environnement _ALLOW_XDEBUG sous la forme nom=valeur
     * 
     * @return string
     */
    private function getEnvAllow(): string
    {
        return $this->envAllowXdebug . '=' . getenv($this->envAllowXdebug);
    }

    /**
     * Renvoie le statut et la version de Xdebug
     * 
     * @return string
     */
    private function getLoadedMessage(): string
    {
        $textIfLoaded = "La version chargé de l'extension Xdebug est : %s";
        $textIfUnloaded = "L'extension Xdebug n'est pas chargé.";

        return !isset($this->loaded) ? $textIfUnloaded : sprintf($textIfLoaded, $this->loaded);
    }
}