<?php namespace SamagTech\Exceptions;

/**
 * Eccezione utlizzata per la gestione di errori durante la modifica di risorse
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 * @extends \SamagTech\Exceptions\BaseCrudException
 */
class UpdateException extends BaseCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     */
    protected string $customMessage = 'Errore di modifica della risorsa';

}