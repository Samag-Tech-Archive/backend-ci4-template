<?php namespace SamagTech\Contracts;

/**
 * Definizione modello per la gestione di file.
 *
 * @interface
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface FileModelInterface {

    //---------------------------------------------------------------------------------

    /**
     * Restituisce i dati di un file
     *
     * @param int|string  $fileID  Idenficativo del file
     *
     * @return array<string,mixed>|null    Dati del file oppure NULL se non esiste
     */
    public function getFileByID(int|string $fileID) : ?array;

    //---------------------------------------------------------------------------------

    /**
     * Restituisce tutti i dati dei file legati ad un risorsa.
     *
     * @param int|string $resourceID   Identificativo risorsa
     *
     * @return array<int,array<string,mixed>|null
     */
    public function getFilesByResource(int $resourceID) : ?array;

    //---------------------------------------------------------------------------------
}