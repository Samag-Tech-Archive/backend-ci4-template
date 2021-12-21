<?php namespace SamagTech\Contracts;

use \CodeIgniter\HTTP\Response;

/**
 * Definizione di un controller
 *
 * @extends \SamagTech\Contracts\Factory
 *
 * @interface
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 */
interface ControllerFactory extends Factory {

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la creazione di una nuova risorsa.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function create() : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la visualizzazione di una lista di risorse
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function retrieve() : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la visualizzazione di una singola risorsa.
     *
     * @param int|string $id   Identificativo risorsa.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function retrieveById(int|string $id) : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la modifica di una risorsa.
     *
     * @param int|string $id   Identificativo risorsa.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function update(int|string $id) : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la cancellazione di una risorsa.
     *
     * @param int|string $id  Identificativo risorsa
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete(int|string $id) : Response;

    //---------------------------------------------------------------------------------------------------

    /**
     * Route per l'esportazione excel della lista risorse
     *
     * @return  \CodeIgniter\HTTP\Response
     */
    public function export() : Response;

    //---------------------------------------------------------------------------------------------------
}