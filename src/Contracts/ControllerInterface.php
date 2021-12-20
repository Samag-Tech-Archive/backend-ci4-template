<?php namespace SamagTech\Contracts;

use \CodeIgniter\HTTP\Response;

/**
 * Definizione di un controller
 *
 * @extends \SamagTech\Crud\Contracts\Factory
 * @interface
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 */
interface ControllerInterface extends Factory {

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

    //---------------------------------------------------------------------------------------------------

    /**
     * Route per l'esportazione excel della lista
     *
     * @return  \CodeIgniter\HTTP\Response
     */
    public function export() : Response;

    //---------------------------------------------------------------------------------------------------
}