<?php namespace SamagTech\Exceptions;

/**
 * Eccezione utilizzata in caso di risorse non trovate all'interno del DB
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 * @extends \SamagTech\Exceptions\BaseCrudException
 */
class ResourceNotFoundException extends BaseCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     */
    protected string $customMessage = 'La risorsa non è stata trovata';

    /**
     * Codice di errore da restituire come status http
     *
     * @var int
     *
     * Default 400-Not Found
     */
    protected int $httpCode = 404;

}