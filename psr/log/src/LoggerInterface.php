<?php declare(strict_types=1);

namespace Psr\Log;


/**
 * Décrit une instance de journalisation.
 * 
 * Le message DOIT être une chaîne ou un objet implémentant __toString().
 * Le message PEUT contenir des espaces réservés sous la forme : {foo} 
 * où foo sera remplacé par les données de contexte dans la clé "foo". 
 * 
 * Le tableau de contexte peut contenir des données arbitraires. 
 * La seule hypothèse qui peut être faite par les implémenteurs est 
 * que si une instance d'exception est donnée pour produire une trace de pile, 
 * elle DOIT être dans une clé nommée "exception".
 */
interface LoggerInterface
{
    /**
     * Le système est inutilisable
     * @param string|\Stringable $message
     * @param mixed[] $context
     * @return void
     */
    public function emergency(string|\Stringable $message, array $context = []): void;

    public function alert(string|\Stringable $message, array $context = []): void;
    public function critical(string|\Stringable $message, array $context = []): void;

    public function error(string|\Stringable $message, array $context = []): void;

    public function warning(string|\Stringable $message, array $context = []): void;
    public function notice(string|\Stringable $message, array $context = []): void;
    public function info(string|\Stringable $message, array $context = []): void;
    public function debug(string|\Stringable $message, array $context = []): void;

    public function log($level, string|\Stringable $message, array $context = []): void;
}