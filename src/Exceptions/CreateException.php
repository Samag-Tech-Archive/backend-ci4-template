<?php namespace SamagTech\Exceptions;

/**
 * Eccezione utilizzata in fase di creazione di risorse e file.
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 * @extends \SamagTech\Exceptions\BaseCrudException
 */
class CreateException extends BaseCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     */
    protected string $customMessage = 'Errore durante la creazione della risorsa';

}