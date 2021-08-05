<?php namespace SamagTech\Crud\Core;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Definizione dell'interfaccia per l'upload e il download di file
 * 
 * @interface 
 *
 * @author Alessandro Marotta
 */
interface FileService {

    //-----------------------------------------------------------------------
    
    /**
     * Funzione per l'uploads di uno o più file
     * 
     * @param IncomingRequest $request      Dati della richiesta
     * @param int             $resourceId   Identificativo a cui sono legati i file
     * 
     * @throws ValidationException          Solleva questa eccezione se è fallita la validazione
     * @throws ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste quando l'id è diverso da null
     * @throws UploadException              Solleva quest'eccezione se c'è stato un errore l'uploads
     * 
     * @return bool     TRUE se l'upload è stato effettuato con successo, FALSE altrimenti
     */
    public function uploads(IncomingRequest $request, ?int $resourceID = null) : bool;

    //-----------------------------------------------------------------------

    /**
     * Funzione per il download di un file.
     * 
     * @param IncomingRequest $request      Dati della richiesta
     * @param int             $fileId       Identificativo del file da scaricare
     * 
     * @throws DownloadException            Solleva quest'eccezione se c'è stato un errore il download
     * @throws ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste
     * 
     * @return array    Contiene il path per il download e il nome originale del file
     */
    public function download(IncomingRequest $request, int $fileID ) : array;

    //-----------------------------------------------------------------------

    /**
     * Funzione per
     * 
     * @param IncomingRequest $request      Dati della richiesta
     * @param int             $fileId       Identificativo file da cancellare
     * 
     * @throws DeleteException              Solleva quest'eccezione se c'è stato un errore durante la cancellazione del file
     * @throws ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste quando l'id è diverso da null
     * 
     * @return bool     TRUE se la cancellazione è stata effettuata con successo, FALSE altrimenti
     */
    public function deleteFile(IncomingRequest $request, int $fileID) : bool;


    //-----------------------------------------------------------------------

    /**
     * Funzione per il download di tutti i file legati ad una risorsa
     * 
     * @param IncomingRequest $request      Dati della richiesta
     * @param int             $resourceId   Identificativo della risorsa a cui sono legati i file
     * 
     * 
     * @throws DownloadException            Solleva quest'eccezione se c'è stato un errore il download
     * @throws ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste
     * 
     * @return string   Path del file
     */
    public function downloadAllResource(IncomingRequest $request, int $resourceID) : string;

    //-----------------------------------------------------------------------

    /**
     * Funzione per il download di più file
     * 
     * @param IncomingRequest   $request    Dati della richiesta
     * 
     * @throws DownloadException            Solleva quest'eccezione se c'è stato un errore il download
     * @throws ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste
     * 
     * @return string    Path del file
     */
    public function downloadFiles(IncomingRequest $request) : string;

    //-----------------------------------------------------------------------



}