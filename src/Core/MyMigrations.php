<?php

namespace SamagTech\Crud\Core;

use CodeIgniter\Database\Migration;

/**
 * Classe da esterndere per le migrazioni di tabelle a cui 
 * è possibile aggiungere dati accessori
 * 
 * @method addFieldsAccessories(array $fields) : array   Aggiunge i dati accessori alla classe
 * 
 */
abstract class MyMigrations extends Migration {

    /**
     * Flag che indica se creare la colonna created_date
     * 
     * @access protected
     * @var bool
     */
    protected bool $createdDate = true;

    /**
     * Flag che indica se creare la colonna updated_date
     * 
     * @access protected
     * @var bool
     */
    protected bool $updatedDate = true;

    /**
     * Flag che indica se creare la colonna sys_updated_date
     * 
     * @access protected
     * @var bool
     */
    protected bool $sysUpdatedDate = true;

    /**
     * Flag che indica se creare la colonna deleted_date
     * 
     * @access protected
     * @var bool
     */
    protected bool $deletedDate = false;

    /**
     * Flag che indica se creare la colonna created_by
     * 
     * @access protected
     * @var bool
     */
    protected bool $createdBy = false;

    /**
     * Flag che indica se creare la colonna updated_by
     * 
     * @access protected
     * @var bool
     */
    protected bool $updatedBy = false;

    /**
     * Lista delle query per l'aggiunta delle colonne
     * 
     * @var array
     * @access protected
     */
    protected array $columnDefinition = [
        'created_date'      => 'created_date DATETIME DEFAULT now()',
        'sys_updated_date'  => 'sys_updated_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        'updated_date'      => 'updated_date DATETIME DEFAULT NULL',
        'created_by'        => 'created_by MEDIUMINT UNSIGNED NOT NULL',
        'updated_by'        => 'updated_by MEDIUMINT UNSIGNED DEFAULT NULL',
    ];

    //--------------------------------------------------------------------------------

    /**
     * Funzione per aggiungere le colonne accessorie
     * 
     * @param array $fields     Array contentente i campi della tabella
     * 
     * @access protected
     * 
     * @return array  Ritorna l'array con i campi accessori
     */
    protected function addFieldsAccessories(array $fields) : array {
        
        // Per ogni flag impostato a true aggiungo la colonna accessoria
        if ( $this->createdDate ) {
            array_push($fields, $this->columnDefinition['created_date']);
        }

        if ( $this->updatedDate ) {
            array_push($fields, $this->columnDefinition['updated_date']);
        }

        if ( $this->sysUpdatedDate ) {
            array_push($fields, $this->columnDefinition['sys_updated_date']);            
        }

        if ( $this->createdBy ) {
            array_push($fields, $this->columnDefinition['created_by']);
        }

        if ( $this->updatedBy ) {
            array_push($fields, $this->columnDefinition['updated_by']);
        }

        return $fields;
    }

    //--------------------------------------------------------------------------------

}