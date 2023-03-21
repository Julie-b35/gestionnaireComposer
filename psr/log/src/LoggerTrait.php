<?php declare(strict_types=1);

namespace Psr\Log;

/**
 * Il s'agit d'un simple trait Logger que les classes incapables d'étendre AbstractLogger
 *  (parce qu'elles étendent une autre classe, etc.) peuvent inclure.
 * 
 * Il délègue simplement toutes les méthodes spécifiques au niveau de journalisation à la méthode `log` 
 * pour réduire le code passe-partout qu'un simple enregistreur qui fait la même chose 
 * avec les messages quel que soit le niveau d'erreur doit implémenter.
 */
trait LoggerTrait
{
    /**
     * Le système est inutilisable
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }


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
    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }


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
    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }


    /**
     * Erreur d’exécution, ceci ne requière pas une action immédiate cependant elle
     * doit être logé et monitoré/
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }


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
    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }


    /**
     * Évènements normaux mais significatif.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }


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
    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }


    /**
     * Information débogage détaillé.
     * 
     * @param string|\Stringable $message
     * @param mixed[] $context
     * 
     * @return void
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

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
    abstract public function log($level, string|\Stringable $message, array $context = []): void;
}