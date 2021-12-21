<?php namespace SamagTech\Contracts;

use CodeIgniter\HTTP\Response;

/**
 * Definizione di un controller con la gestione dei file.
 *
 * @interface
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 */
interface FileController {

    //--------------------------------------------------------------------------------------------

    /**
     * Route il caricamento di uno o più file.
     *
     * @param int|string $resourceID   Identificativo della risorsa a cui associare i file (Opzionale)
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function uploads(int|string $resourceID = null) : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per il download di un file
     *
     * @param int|string $fileID   Identificativo del file da scaricare
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function download(int|string $fileID) : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la cancellazione del file
     *
     * @param int|string $fileID   Identificativo del file da cancellare
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function deleteFile(int|string $fileID) : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per il download massivo di tutti i file legati ad una risorsa
     *
     * @param int|string $resourceID   Identificativo della risorsa.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function downloadAllByResource(int|string $resourceID) : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per il download di una lista di file.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function downloadFiles() : Response;

    //--------------------------------------------------------------------------------------------

}