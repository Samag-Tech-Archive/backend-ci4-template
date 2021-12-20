<?php namespace SamagTech\Exceptions;

/**
 * Eccezione utilizzata per le gestione degli errori durante i download di file
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 * @extends \SamagTech\Exceptions\BaseCrudException
 */
class DownloadException extends BaseCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     */
    protected string $customMessage = 'Errore durante il download della risorsa';


}