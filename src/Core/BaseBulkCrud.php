<?php namespace SamagTech\Core;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\I18n\Time;
use SamagTech\Crud\Exceptions\CreateException;
use SamagTech\Crud\Exceptions\DeleteException;
use SamagTech\Crud\Exceptions\ResourceNotFoundException;
use SamagTech\Crud\Exceptions\UpdateException;

/**
 * Classe astratta da estendere per l'aggiunta delle funzionalità bulk
 *
 * @implements BulkService
 *
 * @abstract
 * @author Alessandro Marotta
 */
abstract class BaseBulkCrud extends CRUDService implements BulkService {

    /**
     * Chiave per accedere ai dati da inserire in modo bulk
     *
     * @example Richiesta: ```json
     *              {
     *                  "examples" : [{
     *                      ...
     *                   },
     *                  {
     *                      ...
     *                  }]
     *              }
     *          ```
     *          La chiave sarà 'examples'
     *
     * @var string|null
     * @access protected
     */
    protected ?string $keyBulk = null;

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function bulkCreate(IncomingRequest $request) : bool {

        // Recupero i dati dalla richiesta
        $data = $request->getJSON(true);

        if ( ! $this->isSetKeyBulk($data) ) {
            throw new CreateException('La chiave per l\'accesso all\'oggetto non è settata');
        }

        // Recupero solo i dati della chiave bulk
        $data = $data[$this->keyBulk];

        if ( ! is_array($data) ) {
            throw new CreateException('I dati sono malformati');
        }

        // Inizializzo la transazione
        $this->db->transStart();

        /**
         * Per ogni riga da inserire controllo che i dati siano validi
         * ed eseguo tutte le callback possibili.
         *
         * Logica vuole che vengano inseriti tutti o nessuno
         */
        foreach ( $data as $d ) {

            // Eseguo il check della validazione
            $this->checkValidation($d,'insert');

            // Callback pre-inserimento
            $modifyData = $this->preInsertCallback($d);

            // Callback per estrarre dati esterni alla riga
            $extraInsert = $this->getExtraData($modifyData);

            // Se è impostato la colonna created_by aggiungo l'utente corrente
            if ( $this->model->useCreatedBy ) {
                $modifyData = array_merge($modifyData, [
                    'created_by' => $this->currentUser->id,
                ]);
            }

            // Inserisco i dati
            $id = $this->model->insert($modifyData);

            $this->logger->create('create', $id, $modifyData);

            // Callback post-inserimento
            $this->postInsertCallback($id, $modifyData, $extraInsert);

        }

        // Termino la transazione
        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    //--------------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function bulkUpdate(IncomingRequest $request) : bool {

        // Recupero i dati dalla richiesta
        $data = $request->getJSON(TRUE);

        if ( ! $this->isSetKeyBulk($data) ) {
            throw new UpdateException('La chiave per l\'accesso all\'oggetto non è settata');
        }

        // Recupero solo i dati della chiave bulk
        $data = $data[$this->keyBulk];

        if ( ! is_array($data) ) {
            throw new UpdateException('I dati sono malformati');
        }

        // Controllo che gli identificativi esistano e siano validi
        if (  ( $existsData = $this->checkExistsIds($data) ) === false ) {
            throw new UpdateException('Il campo id non è impostato oppure non esistono uno o più risorse');
        }

        // Inizializzo la transazione
        $this->db->transStart();

        foreach ( $data as &$d ) {

            // Eseguo il check della validazione
            $this->checkValidation($data,'update');

            $resourceID = $d['id'];
            unset($d['id']);

            // Callback pre-modifica
            $modifyData = $this->preUpdateCallback($resourceID, $d);

            // Callback per estrarre dati esterni alla riga
            $extraUpdate = $this->getExtraData($modifyData);

            // Se è impostato la colonna updated_by aggiungo l'utente corrente
            if ( $this->model->useUpdatedBy ) {
                $modifyData = array_merge($modifyData, [
                    'updated_by' => $this->currentUser->id,
                ]);
            }

            $d['updated_date'] = Time::now();

            $this->model->update($resourceID,$d);

            // Check per il logger
            $this->logger->create('update', $resourceID, $existsData[$resourceID], $modifyData);

            // Callback post-modifica
            $this->postUpdateCallback($resourceID, $modifyData, $extraUpdate);
        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function bulkDelete(IncomingRequest $request) : bool {

        // Recupero i dati dalla richiesta
        $data = $request->getJSON(TRUE);

        if ( ! $this->isSetKeyBulk($data) ) {
            throw new UpdateException('La chiave per l\'accesso all\'oggetto non è settata');
        }

        // Recupero solo i dati della chiave bulk
        $data = $data[$this->keyBulk];

        if ( ! is_array($data) ) {
            throw new DeleteException('I dati sono malformati');
        }

        // Controllo che gli identificativi esistano e siano validi
        if (  ( $existsData = $this->checkExistsIds($data) ) === false ) {
            throw new DeleteException('Il campo id non è impostato oppure non esistono uno o più risorse');
        }

        // Inizializzo la transazione
        $this->db->transStart();

        foreach ( $data as $d ) {

            $resourceID = $d['id'];

            // Funzione pre-cancellazione
            $this->preDeleteCallback($resourceID, $existsData[$resourceID]);

            $this->model->delete($resourceID);

            // Check per il logger
            $this->logger->create('delete', $resourceID, $existsData[$resourceID]);


            // Callback per ulteriori azioni post cancellazione
            $this->postDeleteCallback($resourceID, $existsData[$resourceID]);

        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false;

    }


    //---------------------------------------------------------------------------------------------------

    /**
     * Controlla se la chiave per l'accesso ai dati bulk sia settata
     * e non sia NULL
     *
     * @access private
     *
     * @param array $data   Dati della richiesta
     *
     * @return bool
     */
    private function isSetKeyBulk(array $data) : bool {
        return  ! is_null($this->keyBulk) && isset($data[$this->keyBulk]);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Controlla l'esistenza dei dati tramite identificativo.
     *
     * @access private
     *
     * @param array<string,mixed> $data     Dati provenienti dalla richiesta
     *
     * @return array<int,mixed>|false Array dei dati trovati accessibile tramite identificativo,
     *                                  False altrimenti in caso di assenza di dati
     */
    private function checkExistsIds(array $data) : array|false {

        $data_ids = array_column($data, 'id');

        if ( empty($data_ids) ) {
            return false;
        }

        $existsData = $this->model->find($data_ids);

        if ( count($data_ids) != count($existsData) ) {
            return false;
        }

        return array_format_by_key($existsData, 'id');
    }

    //---------------------------------------------------------------------------------------------------
}