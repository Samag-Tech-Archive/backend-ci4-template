<?php namespace SamagTech\Crud\Core;

use \CodeIgniter\HTTP\Response;

/**
 * Interfaccia per la definizione delle route principali 
 * di un controller CRUD.
 * 
 * @author Alessandro Marotta
 * 
 */
interface ServiceControllerInterface extends Factory {

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la creazione di una nuova risorsa
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function create() : Response; 

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la lettura dei dati di una o più risorse
     * 
     * @param int $id   Identificativo della singola risorsa ( Default 'null')
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function retrieve(int $id = null) : Response; 

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la modifica di una risorsa
     * 
     * @param int $id   Identificativo della risorsa da modifica
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function update(int $id ) : Response; 

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la cancellazione di una risorsa
     *  
     * @param int $id  Identificativo della risorsa da cancellare
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete(int $id) : Response; 
}