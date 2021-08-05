<?php namespace SamagTech\Crud\Exceptions;

/**
 * Eccezione per il download dei file
 * 
 * @author Alessandro Marotta
 */
class DownloadException extends AbstractCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     * 
     * @var string
     */
    protected string $customMessage = 'Errore durante il download della risorsa';
    

}