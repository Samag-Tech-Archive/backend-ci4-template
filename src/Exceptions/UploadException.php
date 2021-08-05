<?php namespace SamagTech\Crud\Exceptions; 

/**
 * Eccezione per l'upload dei file
 * 
 * @author Alessandro Marotta
 */
class UploadException extends AbstractCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     * 
     * @var string
     */
    protected string $customMessage = 'Errore durante l\'upload della risorsa';
    
}