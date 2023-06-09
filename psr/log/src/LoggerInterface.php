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
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function emergency(string|\Stringable $message, array $context = []): void;

    /**
     * Une action doit être effectué immédiatement.
     * 
     * Exemple : Site web down, base de donnée inaccessible, etc.
     * Il devrais déclenché des alerte SMS et vous avertir.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function alert(string|\Stringable $message, array $context = []): void;

    /**
     * Condition Critique.
     * 
     * Exemple : Application momentanément indisponible, exception inattendu.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function critical(string|\Stringable $message, array $context = []): void;

    /**
     * Erreur d’exécution, ceci ne requière pas une action immédiate cependant elle
     * doit être logé et monitoré/
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function error(string|\Stringable $message, array $context = []): void;

    /**
     * Occurrences exceptionnel qui ne sont pas des erreurs.
     * 
     * Exemple : L'utilisation d'API obsolète, mauvaise utilisation d'une Api, choses indésirables
     * qui ne sont pas forcément des erreurs.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function warning(string|\Stringable $message, array $context = []): void;

    /**
     * Évènements normaux mais significatif.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function notice(string|\Stringable $message, array $context = []): void;

    /**
     * Évènements intéressent.
     * 
     * Exemple: Logs Connexion utilisateur,logs SQL.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function info(string|\Stringable $message, array $context = []): void;

    /**
     * Information débogage détaillé.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function debug(string|\Stringable $message, array $context = []): void;

    /**
     * Log avec un niveau arbitraire.
     * 
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void*
     * 
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void;
}