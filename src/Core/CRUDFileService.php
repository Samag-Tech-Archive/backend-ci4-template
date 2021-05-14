<?php namespace SamagTech\Crud\Core;

use CodeIgniter\HTTP\IncomingRequest;
use SamagTech\Crud\Exceptions\CreateException;
use SamagTech\Crud\Exceptions\DeleteException;
use SamagTech\Crud\Exceptions\DownloadException;
use SamagTech\Crud\Exceptions\UploadException;
use SamagTech\Crud\Exceptions\ValidationException;
use SamagTech\Crud\Exceptions\ResourceNotFoundException;
use \ZipArchive;

/**
 * Classe astratta che implementa i servizi CRUD di default
 * 
 * @implements Service
 * @author Alessandro Marotta
 */
abstract class CRUDFileService extends CRUDService implements FileService {

    /**
     * Stringa contentente il nome del modello
     * 
     * @var string
     * @access protected
     */
    protected ?string $fileModelName = null;

    /**
     * Modello per la gestione dei file
     * 
     * @var CRUDModel
     * @access protected 
     */
    protected ?CRUDModel $fileModel = null;

    /**
     * Array di validazione per l'upload dei file.
     * 
     * @var array
     * @access protected
     */
    protected array $validationsUploadsRules = [];

    /**
     * Array contententi i messaggi custom di validazione
     * in fase di upload.
     * 
     * @var array
     * @access protected
     */
    protected array $validationsUploadsCustomMessage = [];

    //---------------------------------------------------------------------------------

    /**
     * Costruttore.
     * 
     */
    public function __construct() {
        parent::__construct();

        // Imposto il modello per i file
        if ( is_null($this->fileModelName) ) {
            die('Il modello per i file non è settato');
        }
        else {
            $this->fileModel = model($this->configModel[$this->fileModelName]);
        }
    }

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     */
    public function uploads(IncomingRequest $request, int $resourceID): bool {

        // Validazione dei file
        $this->checkValidationFile($request);

        // Recupero i dati del documento
        $document = $this->model->setId($resourceID)->get();

        // Recupero i file
        $files = $request->getFileMultiple('files');

        // Dati da inserire nel database
        $toInsert = [];

        // Carico ogni file
        foreach ( $files as $file ) {

            // Se il file non è valido lancio un eccezione
            if ( ! $file->isValid() ) throw new UploadException(); 

            if ( ! $file->hasMoved() ) {

                $hashName = $file->getRandomName();
                $file->move($this->appConfig->uploadsPath.$document['folder'], $hashName);

                // Genero i dati da inserire nel database
                $toInsert[] = [
                    'name'          =>  $file->getClientName(),
                    'hash_name'     =>  $hashName,
                    'extension'     =>  $file->getClientExtension(),
                    'size'          =>  $file->getSizeByUnit('mb'),
                    'document_id'   =>  $resourceID
                ];
            }   

        }

        // Inserisco i dati nel db
        if ( $this->fileModel->insertBatch($toInsert) == false ) {
            throw new CreateException();
        } 

        // Check per il logger
        if ( $this->appConfig->activeLogger ) {
            $this->logger->createLog('upload', $resourceID, $toInsert);
        }

        return true;

    }

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     */
    public function download(IncomingRequest $request, int $fileID): array {

        $file = null;

        // Controllo se il file esiste
        if ( is_null($file = $this->fileModel->getFileByID($fileID)) ) {
            throw new ResourceNotFoundException('Il file non esiste');
        }
        
        return [
            'path'          => $file['document_folder'].'/'.$file['hash_name'],
            'original_name' => $file['name']
        ];
    }

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     */
    public function deleteFile(IncomingRequest $request, int $fileID): bool {

        // Dati del file
        $file = null;
        
        // Controllo se il file esiste
        if  ( is_null($file = $this->fileModel->find($fileID)) ) {
            throw new ResourceNotFoundException('Il file non esiste');
        }

        // Recupero i dati del documento
        $document = $this->model->find($file['document_id']);
        
        // Controllo se il documento è stato confermato
        if ( $document['is_confirmed'] ) {
            throw new DeleteException('Non puoi cancellare i file se il documento è confermato');
        }

        $this->db->transStart();

        // Cancello la riga
        $isDelete = $this->fileModel->delete($fileID) != false;
        
        if ( $isDelete ) { 
        
            // Cancello il file
            if ( ! unlink($this->appConfig->uploadsPath.$document['folder'].'/'.$file['hash_name']) ) {
                throw new DeleteException('Errore durante la cancellazione del file');
            } 
        }

        // Check per il logger
        if ( $this->appConfig->activeLogger ) {
            $this->logger->createLog('delete_file',$fileID, $file);
        }

        $this->db->transComplete();

        // Se la transazione è fallita sollevo un eccezione
        if ( $this->db->transStatus() === FALSE ) {
            throw new DeleteException();
        }

        return $isDelete;
    }

    //---------------------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * 
     */
    public function downloadAll( IncomingRequest $request, int $resourceId ) {
        
        $document = null;

        // Controllo se è installato il plugin di zip
        if ( ! \extension_loaded('zip') ) throw new DownloadException('L\'estensione Zip non è installata');

        // Controllo se il file esiste
        if ( is_null($document = $this->model->find($resourceId)) ) {
            throw new ResourceNotFoundException('Il documento non esiste');
        }

        // Controllo se esiste la cartella temporanea
        if ( ! file_exists($this->appConfig->tmpPath) ) {
            mkdir($this->appConfig->tmpPath);
        }

        // Recupero tutti i file del documento
        $files = $this->fileModel->getFileByMultiDocumentsID([$document['id']]);

        if ( is_null($files) ) {
            throw new DownloadException('Non ci sono file da scaricare');
        }

        // Creo il nome dello zip con l'identificativo
        $zipname = 'documento_n_'.$document['id'].'.zip';

        $zip = new ZipArchive();

        if ($zip->open($this->appConfig->tmpPath.$zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE ) {
            throw new DownloadException('Errore creazione archivio');
        }

        // Salvo i file aggiunti
        $filesAdded = [];

        // Nome del file da aggiungere all'archivio
        $filename = null;

        foreach ( $files as $file ) {

            /**
             * Controllo se ci sono file con lo stesso nome da inserire nell'archivio.
             * 
             * Salvo i file caricato nell'array con chiave il nome del file e come valore il numero di file con lo stesso nome.
             * 
             * Se il file non si trova negli aggiunti lo inserisco e gli imposto come valore 1 essendo il primo,
             * altrimenti se il file già esiste negli aggiunti recupero il numero attuale di file con lo stesso nome
             * ed incremento. Successivamente viene eliminata l'estensione e viene aggiunto al nome il numero duplicato del nome
             * con la seguente sintassi "-NUMERO_ATTUALI" e viene riaggiunta l'estensione.
             * 
             * Es. 3 file di nome "Test.pdf"
             *  L'archivio conterrà:
             *      - Test.pdf
             *      - Test-1.pdf
             *      - Test-2.pdf
             */
            if ( ! in_array($file['name'], array_keys($filesAdded), true) ) {
                
                if ( ! isset($filesAdded[$file['name']] ) ) {
                    $filesAdded[$file['name']] = 1;
                }

                $filename = $file['name'];
            }
            else {

                // Recupero il numero di file con lo stesso nome già caricati
                $actualFileWithSameName = $filesAdded[$file['name']];

                $filesAdded[$file['name']] += 1;

                $nameWithoutExtension = str_replace('.'.$file['extension'], '' , $file['name']);

                // Imposto il nuovo nome
                $filename = $nameWithoutExtension.'-'.$actualFileWithSameName.'.'.$file['extension'];        
            }

            // Aggiunto il file all'archivio.
            $zip->addFile($this->appConfig->uploadsPath.$document['folder'].'/'.$file['hash_name'], $filename);
        }

        $zip->close();
        
        return [
            'path' =>   $zipname,
        ];
    }

    //---------------------------------------------------------------------------------

    /**
     * Funzione che gestisce la validazione per i file
     * 
     * @access protected
     * @param IncomingRequest $request  Richiesta del client
     * @throws  ValidationException Solleva un eccezione in caso di fallimento della validazione
     * @return void
     */
    protected function checkValidationFile(IncomingRequest $request) {

        // Controllo se sono settate le regole di validazione
        if ( isset($this->validationsUploadsRules) && ! empty($this->validationsUploadsRules) ) {

            /**
             * Istanzio la libreria di validazione 
             * ed eseguo la validazione
             * 
             */
            $validation = \Config\Services::validation();
        
            $validation->setRules($this->validationsUploadsRules,$this->validationsUploadsCustomMessage);

            // Lancio la validazione, se fallisce lancio un eccezione
            if ( ! $validation->withRequest($request)->run()) {
                
                throw new ValidationException($validation->getErrors());
            }
        }
    }


}