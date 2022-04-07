<?php namespace SamagTech\Crud\Config;

use CodeIgniter\Config\BaseConfig;
use SamagTech\Crud\Libraries\RestfulCaller\AsyncRestful;
use SamagTech\Crud\Libraries\RestfulCaller\SyncRestful;

/**
 * Configurazione per la libreria RestfulCaller
 *
 * @extends CodeIgniter\Config\BaseConfig
 */
class RestfulCaller extends BaseConfig {

    /**
     * Callers di default
     *
     * @var array<string,string>
     */
    public array $defaults = [
        'sync'  => SyncRestful::class,
        'async' => AsyncRestful::class
    ];

    /**
     * Lista di caller custom
     *
     * @var array<string,string>
     */
    public array $customCallers = [];
}