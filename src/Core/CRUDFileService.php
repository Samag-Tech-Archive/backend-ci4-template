<?php namespace SamagTech\Crud\Core;

use CodeIgniter\HTTP\Files\UploadedFile;
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

    /**
     * Path per l'upload
     *
     * @var string
     * @access protected
     * Default 'null'
     */
    protected ?string $pathUpload = null;

    /**
     * Array contenente il nome dei file che hanno subito un problema durante l'upload
     *
     * @var string[]
     * @access protected
     */
    protected array $errors = [];

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
            $this->fileModel = model($this->fileModelName);
        }

        // Setto il path di default
        $this->pathUpload = $this->pathUpload ?? $this->appConfig->uploadsPath;
    }

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function uploads(IncomingRequest $request, ?int $resourceID = null): bool {

        // Validazione dei file
        $this->checkValidationFile($request);

        // Recupero i dati della risorsa
        $resource = is_null($resourceID) ? null : $this->model->setId($resourceID)->get();

        // Recupero i file
        $files = $request->getFileMultiple('files');

        // Dati da inserire nel database
        $toInsert = [];

        // Callback pre-upload
        $this->preUploadCallback($files, $resource);

        // Carico ogni file
        foreach ( $files as $file ) {

            if ( ! $file->hasMoved() && $file->isValid() ) {

                // Controllo che il nome hashato del file sia univoco
                do {
                    $hashName = $file->getRandomName();
                }
                while( ! $this->fileModel->isUnique('hash_name', $hashName) );

                // Sposta il file
                $file->move($this->pathUpload, $hashName);

                $toInsert[] = $this->createUploadRow($file, $hashName, $resource);

            }
            else {
                $this->errors[] = $file->getClientName();
            }
        }

        // Inserisco i dati nel db
        if ( $this->fileModel->insertBatch($toInsert) == false ) {
            throw new CreateException();
        }

        if ( $this->hasUploadErrors() ) {
            throw new UploadException($this->errors, 'Alcuni file non sono stati caricati');
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

        return $this->getDownloadData($file);
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
        if  ( is_null($file = $this->fileModel->getFileByID($fileID)) ) {
            throw new ResourceNotFoundException('Il file non esiste');
        }

        $this->preDeleteFileCallback($file);

        $this->db->transStart();

        // Cancello la riga
        $deleted = $this->fileModel->delete($fileID) != false;

        if ( $deleted ) {

            // Cancello il file
            if ( ! unlink($this->pathUpload.$file['hash_name']) ) {
                throw new DeleteException('Errore durante la cancellazione del file');
            }
        }

        // Crea il logger
        $this->logger->create('delete_file',$fileID, $file);

        $this->db->transComplete();

        // Se la transazione è fallita sollevo un eccezione
        if ( $this->db->transStatus() === FALSE ) {
            throw new DeleteException();
        }

        return $deleted;
    }

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function downloadAllByResource(IncomingRequest $request, int $resourceID ) :  string {

        $resource = null;

        // Controllo se è installato il plugin di zip
        if ( ! \extension_loaded('zip') ) throw new DownloadException('L\'estensione Zip non è installata');

        // Controllo se il file esiste
        if ( is_null($resource = $this->model->find($resourceID)) ) {
            throw new ResourceNotFoundException('La risorsa non esiste');
        }

        // Recupero il nome da dare allo zip
        $zipname = $request->getGet('zipname') ?? null;

        // Controllo se esiste la cartella temporanea
        if ( ! file_exists($this->appConfig->tmpPath) ) {
            mkdir($this->appConfig->tmpPath);
        }

        // Recupero tutti i file del documento
        $files = $this->fileModel->getFilesByResource($resource['id']);

        if ( is_null($files) ) {
            throw new DownloadException('Non ci sono file da scaricare');
        }

        // Creo il nome dello zip con l'identificativo
        if ( ! is_null($zipname) ) {
            $zipname = str_replace('.zip','', $zipname).'.zip';
        }
        else {
            $zipname = 'Risorsa_n_'.$resource['id'].'.zip';
        }

        $zip = new ZipArchive();

        if ($zip->open($this->appConfig->tmpPath.$zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE ) {
            throw new DownloadException('Errore creazione archivio');
        }

        // Aggiunge i file all'archivio
        $zip = $this->addFileToZip($zip, $files);

        $zip->close();

        return $zipname;
    }

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function downloadFiles(IncomingRequest $request) : string {

        // Controllo se è installato il plugin di zip
        if ( ! \extension_loaded('zip') ) throw new DownloadException('L\'estensione Zip non è installata');

        // Recupero la lista di file
        $fileIDs = $request->getGet('files');

        // Recupero il nome da dare allo zip
        $zipname = $request->getGet('zipname') ?? null;

        // Controllo se esiste la cartella temporanea
        if ( ! file_exists($this->appConfig->tmpPath) ) {
            mkdir($this->appConfig->tmpPath);
        }

        // Recupero tutti i file del documento
        $files = $this->fileModel->find(explode(',', $fileIDs));

        if ( is_null($files) ) {
            throw new DownloadException('Non ci sono file da scaricare');
        }

        // Creo il nome dello zip con l'identificativo
        if ( ! is_null($zipname) ) {
            $zipname = str_replace('.zip','', $zipname).'.zip';
        }
        else {
            $zipname = 'files.zip';
        }

        $zip = new ZipArchive();

        if ($zip->open($this->appConfig->tmpPath.$zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE ) {
            throw new DownloadException('Errore creazione archivio');
        }

        // Aggiunge i file all'archivio
        $zip = $this->addFileToZip($zip, $files);

        $zip->close();

        return $zipname;
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

    //------------------------------------------------------------------------------------------

    /**
     * Funzione per l'aggiunta dei file allo zip
     *
     * @param ZipArchive        $zip    Istanza dello zip
     * @param UploadedFile[]    $files  Lista dei file
     *
     * @return ZipArchive
     */
    protected function addFileToZip(ZipArchive $zip, array $files) : ZipArchive {

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
             $zip->addFile($this->pathUpload.$file['hash_name'], $filename);
         }

        return $zip;
    }

    //------------------------------------------------------------------------------------------

    /**
     * Funziona per controllare se ci sono stati errori
     *
     * @return bool TRUE se ci sono stati errori, altrimenti FALSE
     */
    public function hasUploadErrors() : bool {
        return count($this->errors) > 0;
    }

    //------------------------------------------------------------------------------------------

    /**
     * Funzione che restituisce la lista degli errori che hanno avuto un errore
     *
     * @return string[]
     */
    public function getUploadErrors() : array {
        return $this->errors;
    }


    //------------------------------------------------------------------------------------------

    /**
     * Callback per gestione dei dati pre-upload
     *
     * @access protected
     *
     * @param array         &$files     Lista dei file da caricare
     * @param array|null    $resource  Risorsa da associare (Default null)
     *
     * @return void
     */
    protected function preUploadCallback(array &$files, ?array $resource = null) : void {}

    //------------------------------------------------------------------------------------------

    /**
     * Funzione per la definizione della riga da inserire nel database per i file
     *
     * @access protected
     *
     * @abstract
     *
     * @param UploadedFile  $file           Istanza del file
     * @param string        $hashName       Nome hashato del file caricato
     * @param array|null    $resource       Dati delle risorsa a cui collegare se esiste
     *
     * @return array    Riga da inserire nel database
     */
    abstract function createUploadRow(UploadedFile $file, string $hashName, ?array $resource = null ) : array;

    //------------------------------------------------------------------------------------------

    /**
     * Funzione che restituisce il formato delle risposta al download
     *
     * @access protected
     *
     * @abstract
     *
     * @param array $file   Dati del file
     *
     * @return array    Risposta da inviare al client (Es. ['original_path' => PATH_FILE, 'original_name' => NOME_ORIGINALE])
     */
    abstract function getDownloadData(array $file) : array;

    //------------------------------------------------------------------------------------------

    /**
     * Callback eseguita pre-cancellazione del file
     *
     * @param array $file   File da cancellare
     *
     * @return void
     */
    protected function preDeleteFileCallback(array $file) : void {}

    //------------------------------------------------------------------------------------------

}