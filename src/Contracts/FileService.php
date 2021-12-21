<?php namespace SamagTech\Contracts;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Definizione dell'interfaccia per la gestione di file
 * tramite DB
 *
 * @interface
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface FileService {

    //-----------------------------------------------------------------------

    /**
     * Gestione l'upload di uno o più file.
     *
     * Se è presente la risorsa, allora collega questi file alla risorsa
     *
     * @param CodeIgniter\HTTP\IncomingRequest $request      Dati della richiesta
     * @param int|string|null $resourceID   Identificativo della risorsa a cui legare i file (Opzionali)
     *
     * @throws \SamagTech\Exceptions\ValidationException          Solleva questa eccezione se è fallita la validazione
     * @throws \SamagTech\Exceptions\ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste quando l'id è diverso da null
     * @throws \SamagTech\Exceptions\UploadException              Solleva quest'eccezione se c'è stato un errore l'uploads
     *
     * @return bool     TRUE se l'upload è stato effettuato con successo, FALSE altrimenti
     */
    public function uploads(IncomingRequest $request, int|string|null $resourceID = null) : bool;

    //-----------------------------------------------------------------------

    /**
     * Gestione del download di un singolo file.
     *
     * @param int|string      $fileID   Identificativo del file da scaricare
     *
     * @throws \SamagTech\Exceptions\DownloadException            Solleva quest'eccezione se c'è stato un errore il download
     * @throws \SamagTech\Exceptions\ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste
     *
     * @return array<string,string>|string    Se è un array contiene i dati per il download, altrimenti se è una stringa il path per il download
     */
    public function download(int|string $fileID) : array|string;

    //-----------------------------------------------------------------------

    /**
     * Gestione della cancellazione di un singolo file
     *
     * @param int|string    $fileID     Identicativo del file da cancellare
     *
     * @throws \SamagTech\Exceptions\DeleteException              Solleva quest'eccezione se c'è stato un errore durante la cancellazione del file
     * @throws \SamagTech\Exceptions\ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste quando l'id è diverso da null
     *
     * @return bool     TRUE se la cancellazione è stata effettuata con successo, FALSE altrimenti
     */
    public function deleteFile(int|string $fileID) : bool;


    //-----------------------------------------------------------------------

    /**
     * Gestione download di tutti i file legati ad una singola risorsa
     *
     * @param CodeIgniter\HTTP\IncomingRequest $request      Dati della richiesta
     * @param int|string      $resourceID   Identificativo della risorsa
     *
     *
     * @throws \SamagTech\Exceptions\DownloadException            Solleva quest'eccezione se c'è stato un errore il download
     * @throws \SamagTech\Exceptions\ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste
     *
     * @return string   Path del file
     */
    public function downloadAllByResource(IncomingRequest $request, int|string $resourceID) : string;

    //-----------------------------------------------------------------------

    /**
     * Gestione download di una lista di file
     *
     * @param CodeIgniter\HTTP\IncomingRequest   $request    Dati della richiesta(Dovrà contenere tutti gli identificativi dei file da scaricare)
     *
     * @throws \SamagTech\Exceptions\DownloadException            Solleva quest'eccezione se c'è stato un errore il download
     * @throws \SamagTech\Exceptions\ResourceNotFoundException    Solleva quest'eccezione se la risorsa non esiste
     *
     * @return string    Path del file
     */
    public function downloadFiles(IncomingRequest $request) : string;

    //-----------------------------------------------------------------------



}