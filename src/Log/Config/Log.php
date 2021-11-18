<?php
namespace SamagTech\Log\Config;

/**
 * CodeIgniter Logger
 *
 * @package Log
 * @author  Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 */
class Log extends \CodeIgniter\Config\BaseConfig {

    /**
     * Nome della tabella
     *
     * @var string
     */
    public string $table = "logs";

    /**
     * Tipologia delle operazioni da loggare
     *
     * @var array
     */
    public array  $typeLogs = [
        'create',
        'update',
        'delete',
        'upload',
        'download',
        'delete_file',
    ];

    /**
     * Tipo di log considerato di update.
     *
     * Indica, nel caso di update e che non sollevi eccezioni, che i
     * nuovi dati devono essere settati.
     *
     * @var array
     */
    public $updatedType = [
        'update'
    ];

}