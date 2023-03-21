<?php declare(strict_types=1);

namespace Psr\Log;

/**
 * Il s'agit d'une implémentation simple de Logger dont d'autres Loggers peuvent hériter.
 * 
 * Il délègue simplement toutes les méthodes spécifiques au niveau de journalisation à la méthode `log` 
 * pour réduire le code passe-partout qu'un simple enregistreur qui fait la même chose 
 * avec les messages quel que soit le niveau d'erreur doit implémenter.
 */
abstract class AbstractLogger implements LoggerInterface
{
    use LoggerTrait;
}