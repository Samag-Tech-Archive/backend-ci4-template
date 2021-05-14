<?php namespace SamagTech\Crud\Core;

use CodeIgniter\HTTP\Response;

/**
 * Interfaccia per la definizione delle route principali 
 * per la gestione dei file
 * 
 * @author Alessandro Marotta
 * 
 */
interface FileServiceControllerInterface {

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la creazione di file con upload dei dati
     * 
     * @param int $resourceID   Identificativo della singola risorsa a cui sono legati i file
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function uploads(int $resourceID) : Response; 

    //--------------------------------------------------------------------------------------------

    /**
     * Route per il download di un file
     * 
     * @param int $fileID   Identificativo del file da scaricare
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function download(int $fileID) : Response; 

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la cancellazione del file
     * 
     * @param int $fileID   Identificativo del file da cancellare
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function deleteFile(int $fileID) : Response; 

    //--------------------------------------------------------------------------------------------

    /**
     * Route per il download di tutti i file
     * 
     * @param int $resourceID   Identificativo della risorsa a cui sono legati tutti i file da scaricarew
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function downloadAll(int $resourceID) : Response; 

    //--------------------------------------------------------------------------------------------

}