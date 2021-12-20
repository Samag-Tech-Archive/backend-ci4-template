<?php namespace SamagTech\Crud\Core;

/**
 * Interfaccia per la definizione dei metodi da implemetare sui modelli che gestiscono i file
 *
 * @interface
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface FileModelInterface {

    //---------------------------------------------------------------------------------

    /**
     * Funzione che restituisce i dati di un file
     *
     * @param int  $fileID  Idenficativo del file
     *
     * @return array|null    Dati del file oppure null se non esiste
     */
    public function getFileByID(int $fileID) : ?array;

    //---------------------------------------------------------------------------------

    /**
     * Funzione tutti i file di una risorsa
     *
     * @param int $resourceID   Identificativo risorsa
     *
     * @return array|null
     */
    public function getFilesByResource(int $resourceID) : ?array;

    //---------------------------------------------------------------------------------
}