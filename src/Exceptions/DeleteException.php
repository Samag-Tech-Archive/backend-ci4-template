<?php namespace SamagTech\Exceptions;

/**
 * Eccezione utilizzata per la gestione delle cancellazioni di risorse e file.
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 * @extends \SamagTech\Exceptions\BaseCrudException
 */
class DeleteException extends BaseCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     */
    protected string $customMessage = 'Errore di cancellazione della risorsa';

}