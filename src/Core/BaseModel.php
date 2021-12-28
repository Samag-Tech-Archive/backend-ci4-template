<?php

namespace SamagTech\Core;

use Ramsey\Uuid\Uuid;
use CodeIgniter\Model;
use CodeIgniter\Entity\Entity;
use SamagTech\Core\BaseModelHandler;

/**
 * Estensione del modello di CI4
 * con funzionalità per il CRUD
 *
 *
 */
abstract class BaseModel extends Model {

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
     * Tipologia identificativo
     *
     * @var string
     *
     * @access protected
     *
     * Default 'int'
     */
    protected string $typeId = 'int';

    /**
     * Identificativo corrente assegnato
     *
     * @var int|string|null
     *
     * Default NULL
     * @access private
     */
    private int|string|null $id = null;

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
    public function __construct($DBGroup = 'default') {

        $this->DBGroup = $DBGroup;

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
     * Imposta l'identificativo
     *
     * @param int|string $id   Identificativo
     *
     * @return self
     */
    public function setId($id) : self {
        $this->id = $id;
        return $this;
    }

    //----------------------------------------------------------------------

    /**
     * Restituisce identificativo corrente
     *
     * @return int|string|null
     */
    public function getId() : int|string|null {
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
     * @deprecated Utilizzare find()
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

        // Genera la select
        $this->select(BaseModelHandler::getSelect($identifyTable, $options['select']), false);

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

        // Genera la query con tutte le opzioni ed i parametri
        $this->createRetrieveQuery($options, $identifyTable, $params, $joinFieldsFilters);

        // Se il flag della paginazione è attivo restituisco tutti i dati
        if ( $options['no_pagination'] ) {
            return $this->findAll();
        }

        // Recupero i dati con paginazione
        return $this->findAll($options['limit'],  $options['limit'] * ($options['offset'] > 0 ? $options['offset'] - 1 : 0));

    }

    //---------------------------------------------------------------------

    /**
     * Restituisce i dati di un singola singola risorsa in base ai parametri passati
     *
     * @param int|string        $id         Identificativo risorsa
     * @param array<int,string> $select     Clausola della select (Default [])
     * @param array|null        $joins      Array di join (Default NULL)
     *
     * @return Entity|array<string,mixed>
     */
    public function getWithId (int|string $id, array $select = [], ?array $joins = null ) : Entity|array {

        // Identificativo tabella da inserire nella query
        $identifyTable = $this->getIdentityTable();

        $this->select(BaseModelHandler::getSelect($identifyTable, $select), false);

        // Join per la query
        if ( ! is_null($joins)) {
            foreach ( $joins as $join ) {
                $this->join($join[0], $join[1], ! isset($join[3]) ? 'left' : $join[3]);
            }
        }

        return $this->find($id);

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
     * Restituisce il tipo di ritorno dalle funzioni di ricerca
     *
     * @return string
     */
    public function getReturnType() :string { return $this->returnType; }

    //---------------------------------------------------------------------

    /**
     * Ritorna TRUE se l'identificativo è una stringa,
     * FALSE altrimenti
     *
     * @return bool
     */
    public function isStringId() : bool {
        return $this->typeId == 'string';
    }

    //---------------------------------------------------------------------

    /**
     * Calcola un nuovo identificativo stringa.
     *
     * @return string
     */
    public function getNewStringId () : string {

        $uid = null;

        do {
            $uid = Uuid::uuid1();
        } while ( $this->isUnique('id', $uid));

        return $uid->toString();
    }

    //---------------------------------------------------------------------

	/**
	 *
     * Ritorna il numero di righe trovate durate l'utilizzo
     * del metodo @getWithParams()
	 *
	 * @return int
	 */
	public function getFoundRows() : int {

        // Identificativo tabella da inserire nella query
        $identifyTable = $this->getIdentityTable();

        $this->select('COUNT(*) as n_rows');

        $this->createRetrieveQuery();

        return 0;
	}

	// --------------------------------------------------------------------------

    /**
     * Creare tramite il query builder la base della query sia per la funzione di listaggio
     * sia per il calcolo del numero di righe.
     *
     * @param   array   $options            Array contenente le opzioni settate nel controller per il listaggio(where,join,select ecc...)
     * @param   string  $identityTable      Identificativo della tabella
     * @param   array   $params             Array con i paramentri per la query da eseguire
     * @param   array   $joinFieldsFilters  Array contenente i filtri sui join, traduce il campo in get nella clausola da applicare
     *
     * @return void
     */
    private function createRetrieveQuery (array $options, string $identifyTable, array $params = [], array $joinFieldsFilters = [] ) : void {

        // Opzione per le clausole where non mutabili
        if ( ! is_null($options['where'])) {
            $this->where($options['where']);
        }

        // Join per la query
        if ( ! is_null($options['join'])) {
            foreach ( $options['join'] as $join ) {
                $this->join($join[0], $join[1], ! isset($join[3]) ? 'left' : $join[3]);
            }
        }

        // Eventuali group by
        if ( ! is_null($options['group_by'])) {
            $this->groupBy(BaseModelHandler::getGroupBy($options['group_by']));
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
                     * - like       aggiunge la clausola like con match %valore%
                     * - gte        aggiunge la clausola con >=
                     * - gt         aggiunge la clausola con >
                     * - lte        aggiunge la clausola con <=
                     * - lt         aggiunge la clausola con <
                     * - bool       aggiunge la clausola con IS true|false
                     * - bool_not   aggiunge la clausola con IS NOT true|false
                     * - not_in     aggiunge la clausola WHERE NOT IN (campo, valori) I valori sono dati dal'explode con la virgola
                     * - in         aggiunge la clausola WHERE IN (campo, valori) I valori sono dati dal'explode con la virgola
                     * - null       aggiunge la clausola IS NULL se il valore è TRUE, IS NOT NULL se il valore è FALSE
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
    }

	// --------------------------------------------------------------------------

    /**
     * Restituisce l'identificativo della tabella
     *
     * @return string
     */
    private function getIdentityTable () : string {

        // Identificativo tabella da inserire nella query
        $identifyTable = $this->table;

        // Se non è utilizzato il softDelete allora posso utilizzare l'alias
        if ( ! $this->useSoftDeletes ) {
            $this->from($this->table . ' ' . $this->alias, true);

            $identifyTable = $this->alias;
        }

        return $identifyTable;
    }

	// --------------------------------------------------------------------------

}
