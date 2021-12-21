<?php namespace SamagTech\Exceptions;

/**
 * Eccezione utilizzata in fase di factory del servizio
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 * @extends \SamagTech\Exceptions\BaseCrudException
 */
class BadFactoryException extends BaseCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     */
    protected string $customMessage = 'Il servizio non esiste';

}