<?php

namespace SamagTech\Crud\Core;

use CodeIgniter\Model;

/**
 * Estensione del modello di CI4
 * con funzionalità per il CRUD
 *
 *
 */
class CRUDModel extends Model {

    /**
     * Gruppo database di default
     *
     * @var string
     * Default 'default'
     * @access protected
     */
    protected $DBGroup = 'default';

    /**
     * Tabella su cui agisce il modello
     *
     * @var string
     * Default ''
     * @access protected
     */
    protected $table      = '';

    /**
     * Alias tabella su cui agisce il modello
     *
     * @var string
     * Default ''
     * @access protected
     */
    protected $alias      = '';

    /**
     * Chiave primaria della tabella
     *
     * @var string
     * Default 'id'
     * @access protected
     */
    protected $primaryKey = 'id';

    /**
     * Tipo di ritorno delle query
     *
     * @var string
     * Default 'array'
     * Possibili valori [array, object]
     * @access protected
     */
    protected $returnType     = 'array';

    /**
     * Flag per falsa cancellazione.
     * Se settato a true non cancella la riga
     * ma imposta la data di cancellazione.
     *
     * @var bool
     * Default 'false'
     * @access protected
     */
    protected $useSoftDeletes = false;

    /**
     * Campi importanti per l'inserimento e la modifica
     *
     * @var array
     * Default []
     * @access protected
     */
    protected $allowedFields = [];

    /**
     * Flag per l'uso dei timestamp
     *
     * @var bool
     * Default true
     * @access protected
     */
    protected $useTimestamps = false;

    /**
     * Colonna per la data di creazione della riga
     *
     * @var string
     * Default 'created_date'
     * @access protected
     */
    protected $createdField  = 'created_date';

    /**
     * Colonna per la data di modifica della riga
     *
     * @var string
     * Default 'created_date'
     * @access protected
     */
    protected $updatedField  = 'updated_date';

    /**
     * Colonna per la data di cancellazione della riga,
     * funziona solo se il useSoftDeletes è abilitato
     *
     * @var string
     * Default 'created_date'
     * @access protected
     */
    protected  $deletedField  = 'deleted_date';

    /**
     * Identificativo corrente assegnato
     *
     * @var integer
     * Default NULL
     * @access private
     */
    private ?int $id = null;

    /**
     * Flag che indica se recuperare anche le righe cancellate.
     *
     *  @var bool
     *  Default FALSE
     *  @access private
     */
    private bool $excludeDeleted = false;

    /**
     * Flag che indica se il modello deve tener conto della colonna
     * created_by
     *
     * @access public
     *
     * @var bool
     */
    public bool $useCreatedBy = false;

    /**
     * Flag che indica se il modello deve tener conto della colonna
     * updated_by
     *
     * @access public
     *
     * @var bool
     */
    public bool $useUpdatedBy = false;

    //----------------------------------------------------------------------

    /**
     * Costruttore
     *
     */
    public function __construct() {
        parent::__construct();

        // Controllo che la tabella sia settata
        if ( empty($this->table) || trim($this->table) == '' ) {
            die('La tabella deve essere impostata');
        }

        // Controllo che l'alias sia settato
        if ( empty($this->alias) || trim($this->alias) == '') {
            die('L\'alias della tabella deve essere impostato');
        }

        // Disabilito l'allowFields
        $this->protect(false);

    }

    //----------------------------------------------------------------------

    /**
     * Restituisce la tabella.
     *
     * @return string
     */
    public function getTable() : string { return $this->table; }

    //----------------------------------------------------------------------

    /**
     * Funzione per settare l'identificativo
     *
     * @param integer $id   Identificativo
     * @return self
     */
    public function setId($id) : self {
        $this->id = $id;
        return $this;
    }

    //----------------------------------------------------------------------

    /**
     * Funzione per la restituizione dell'identificativo corrente
     *
     * @return int
     */
    public function getId() : int {
        return $this->id;
    }

    //----------------------------------------------------------------------

    /**
     * Funzione che restituisce un record di una risorsa
     * in base all'identificativo
     *
     * @param array $where  Array con le clausole where (Default [])
     *
     * @return mixed I dati se l'identificativo è impostato, NULL se non ci sono dati
     */
    public function get ( array $where = []) {

        // Se l'identificativo non è nullo allora cerco la risorsa
        if ( ! is_null($this->id)) {
            return $this->find($this->id);

        }

        // Se il flag è false recupero tutte le righe
        if ( ! $this->excludeDeleted ) {
            $this->withDeleted();
        }

        return $this->where($where)->findAll();
    }

    //----------------------------------------------------------------------

    /**
     * Funzione che aggiunge le clausole per includere/escludere gli elementi cancellati
     *
     * @params bool    $exclude    True esclude gli elementi cancellati, False i cancellati
     *
     * @return self
     */
    public function excludeDeleted( bool $exclude = true ) : self {
        $this->excludeDeleted = $exclude;
        return $this;
    }

    //----------------------------------------------------------------------

    /**
     * Funzione che restituisce un singolo elemente in base
     * alle clausole where
    //----------------------------------------------------------------------
     *
     * @params array $where Array con le clausole where ( Default [])
     *
     * @return mixed
     */
    public function row ( array $where = [] ) {
        return $this->where($where)->first();
    }

    //---------------------------------------------------------------------

    /**
     * Funzione che restituisce TRUE se la riga esiste, FALSE altrimenti
     * in base all'identificativo.
     *
     * @param int $id   Identificativo da cercare
     * @return bool
     */
    public function exist( int $id ) : bool {

        if ( ! isset($id) ) {
            $id = $this->id;
        }

        return ! is_null($this->find($id));
    }

    //---------------------------------------------------------------------

    /**
     * Funzione che restituisce la lista dei dati
     * con join, paginazione ed ordinamento.
     *
     * @param   array   $options            Array contenente le opzioni settate nel controller per il listaggio(where,join,select ecc...)
     * @param   array   $params             Array con i paramentri per la query da eseguire
     * @param   array   $joinFieldsFilters  Array contenente i filtri sui join, traduce il campo in get nella clausola da applicare
     * @param   array   $joinFieldsSort     Array contenente i l'ordinamento sui join, traduce il campo in get nella clausola da applicare
     *
     * @return array
     */
    public function getWithParams(array $options, array $params = [], array $joinFieldsFilters = [], array $joinFieldsSort = [] ) {

        // Identificativo tabella da inserire nella query
        $identifyTable = $this->table;

        // Se non è utilizzato il softDelete allora posso utilizzare l'alias
        if ( ! $this->useSoftDeletes ) {
            $this->from($this->table . ' ' . $this->alias, true);

            $identifyTable = $this->alias;
        }

        // Costruisco la query con le opzioni
        if ( ! empty($options['select'])) {

            $select_columns = [];

            foreach ($options['select'] as $key => $select) {

                if ($key == 'func' || strpos($select, '.') !== false ) {
                    $select_columns[] = $select;
                }
                else  if ( strpos($select, '.') === false) {
                    $select_columns[] = $identifyTable.'.'.$select;
                }

            }

            // Per ogni campo della select se non esiste un alias, viene inserito quello della tabella

            $this->select('SQL_CALC_FOUND_ROWS ' . implode(',', $select_columns), FALSE);
        }
        else {
            // Select per calcolare anche il numero di righe totali
            $this->select('SQL_CALC_FOUND_ROWS *', FALSE);
        }

        // Opzione per le clausole where non mutabili
        if ( ! is_null($options['where'])) {
            $this->where($options['where']);
        }

        // Join per la query
        if ( ! is_null($options['join'])) {
            foreach ( $options['join'] as $join ) {
                $this->join($join[0], $join[1], ! isset($join[2]) ? 'left' : $join[2]);
            }
        }

        // Eventuali group by
        if ( ! is_null($options['group_by'])) {
            $this->groupBy(implode(',', $options['group_by']));
        }

        // L'orientamento è dato dal [Campo][Divisore Campo-Ordinamento '.' ][Verso ordinamento]
        foreach( $options['sort_by'] as $sortBy) {

            // Separo il campo dalla clausola
            list($order,$verse) = explode(':', $sortBy);

            /**
             * Se il campo è presente nei campi dei join per l'ordinamento
             * allora decodifico il campo, altrimenti utilizzo l'alias
             * della tabella del modello.
             *
             */
            if ( isset($joinFieldsSort[$order])) {
                $this->orderBy($joinFieldsSort[$order], $verse);
            }
            else {
                $this->orderBy($identifyTable.'.'.$order, $verse);
            }

        }

        // Creo le clausole in base ai paramentri delle query
        if ( ! empty($params)) {
            foreach ( $params as $param => $value ) {

                // Inizializzo la clausola di default
                $clause = null;

                // Controllo se nella stringa ci sono i due :
                if ( strpos($param, ':') === false ) {
                    $field = $param;
                }
                else {
                    // Splitto i parametri GET identificare che tipo di clausola applicare
                    list($field,$clause) = explode(':',$param);
                }

                /**
                 * Controllo se il filtro deve essere applicato su un campo di una tabella joinata
                 *
                 * - Se appartiene ad una tabella joinata allora viene codificato
                 * - altrimenti viene aggiunto l'alias al campo
                 *
                 */
                $trueField = isset($joinFieldsFilters[$field]) ? $joinFieldsFilters[$field] : $identifyTable.'.'.$field;

                // Se non esiste nessuna indicazione oltre al campo viene applicata la clausola where
                if ( is_null($clause) ) {

                    $this->where($trueField,$value);
                }
                else {

                    /**
                     * Se esiste un indicazione ulteriore allora utilizzo la clausola adatta
                     *
                     * - like aggiunge la clausola like con match %valore%
                     * - gte  aggiunge la clausola con >=
                     * - gt   aggiunge la clausola con >
                     * - lte  aggiunge la clausola con <=
                     * - lt   aggiunge la clausola con <
                     *
                     */
                    switch ( $clause ) {
                        case 'like' :
                            $this->like($trueField, $value);
                        break;
                        case 'gte'  :
                            $this->where($trueField . ' >=', $value);
                        break;
                        case 'lte' :
                            $this->where($trueField . ' <=', $value);
                        break;
                        case 'lt' :
                            $this->where($trueField . ' <', $value);
                        break;
                        case 'gt' :
                            $this->where($trueField . ' >', $value);
                        break;
                        case 'bool' :
                            $this->where($trueField . ' IS '. $value);
                        break;
                        case 'bool_not' :
                            $this->where($trueField . ' IS NOT '. $value);
                        break;
                        case 'not_in' :
                            $this->whereNotIn($trueField, explode(',', $value));
                            break;
                        case 'in' :
                            $this->whereIn($trueField, explode(',', $value));
                        break;
                        case 'null' :
                            if ( $value == 'true' ) {
                                $this->where($trueField, null);
                            }
                            else {
                                $this->where($trueField . ' !=', null);
                            }
                        break;
                    }
                }
            }
        }



        // Controllo se è settato l'identificativo univoco, nel caso restituisco un solo array
        if ( ! is_null($options['item_id']) ){

            return $this->where($identifyTable . '.id',$options['item_id'])->first();

        }

        // Se il flag della paginazione è attivo restituisco tutti i dati
        if ( $options['no_pagination'] ) {
            return $this->findAll();
        }

        // Recupero i dati con paginazione
        return $this->findAll($options['limit'],  $options['limit'] * ($options['offset'] > 0 ? $options['offset'] - 1 : 0));


    }

    //---------------------------------------------------------------------

    /**
     * Funzione che controlla se un valore è univoco nella tabella
     *
     * @return bool True se è univoco, altrimenti false
     */
    public function isUnique(string $column, $value) : bool {
        return is_null($this->where($column, $value)->first());
    }

    //---------------------------------------------------------------------

	/**
	 *
     * Ritorna il numero di righe trovate dopo SQL_CALC_FOUND_ROWS ( durante la paginazione)
	 *
	 * @return integer
	 */
	public function getFoundRows() {

        $db = \Config\Database::connect();

		return $db->query('SELECT FOUND_ROWS() as tot_rows')->getRow()->tot_rows;
	}

	// --------------------------------------------------------------------------

}
