<?php namespace SamagTech\Crud\Core;

use SamagTech\Crud\Exceptions\CreateException;
use SamagTech\Crud\Exceptions\DeleteException;
use SamagTech\Crud\Exceptions\ResourceNotFoundException;
use SamagTech\Crud\Exceptions\ValidationException;
use CodeIgniter\HTTP\Response;
use SamagTech\Crud\Exceptions\DownloadException;
use SamagTech\Crud\Exceptions\GenericException;
use SamagTech\Crud\Exceptions\UploadException;

/**
 * Classe astratta per la definizione di un nuovo CRUD con la gestione dei file
 *
 * @extends ServiceController
 * @abstract 
 */
abstract class FileServiceController extends ServiceController implements FileServiceControllerInterface {
    
    /**
     * Array contenente i messaggi di default 
     * per le risposte delle API
     * 
     * @var array
     * @access public 
     */
    public array $messages = [
        'create'        =>  'La risorsa è stata creata',
        'retrieve'      =>  'Lista risorse',
        'update'        =>  'La risorsa è stata modificata',
        'delete'        =>  'La risorsa è stata cancellata',
        'upload_file'   =>  'Il file è stato caricato',
        'download_file' =>  'Il file è stato scaricato',
        'delete_file'   =>  'Il file è stato cancellato',
    ];

    //---------------------------------------------------------------------------
    
    /**
     * Route per la creazione di file con upload dei dati
     * 
     * @param int $resourceID   Identificativo della singola risorsa a cui sono legati i file
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function uploads(int $resourceID): Response {

        try {

            // Eseguo l'upload dei file
            $this->service->uploads($this->request, $resourceID);

        }
        catch(ValidationException $e ) {
            return $this->failValidationErrors($e->getValidationErrors(), $e->getHttpCode());
        } 
        catch(CreateException | UploadException | GenericException $e ) {
            return $this->fail($e->getMessage(), $e->getHttpCode());
        }
        catch(ResourceNotFoundException $e) {
            return $this->failNotFound($e->getMessage(), $e->getHttpCode());
        }

        
        return $this->respondCreated(['item_id' => $resourceID], $this->messages['upload_file']);
    }

    //---------------------------------------------------------------------------

    /**
     * Route per il download di un file
     * 
     * @param int $fileID   Identificativo del file da scaricare
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function download(int $fileID): Response {

        // Conterrà path del file ed il nome originale
        $data = null;

        try {
            $data = $this->service->download($this->request,$fileID);
        }
        catch(ResourceNotFoundException $e) {
            return $this->failNotFound($e->getMessage(), $e->getHttpCode());
        }
        catch(DownloadException | GenericException $e) {
            return $this->fail($e->getMessage(), $e->getHttpCode());
        }

        return $this->respond($data, 200, $this->messages['download_file']);

    }

    //---------------------------------------------------------------------------

    /**
     * Route per la cancellazione del file
     * 
     * @param int $fileID   Identificativo del file da cancellare
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function deleteFile(int $fileID): Response {

        try {
            $this->service->deleteFile($this->request, $fileID);
        }
        catch(ResourceNotFoundException $e) {
            return $this->failNotFound($e->getMessage(), $e->getHttpCode());
        }
        catch(DeleteException | GenericException $e) {
            return $this->fail($e->getMessage(), $e->getHttpCode());
        }

        return $this->respondDeleted(['item_id'  =>  $fileID], $this->messages['delete_file']);
    }


    //---------------------------------------------------------------------------

    /**
     * Route per il download di tutti i file
     * 
     * @param int $resourceID   Identificativo della risorsa a cui sono legati tutti i file da scaricarew
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function downloadAll(int $resourceID): Response {

        // Conterrà path del file ed il nome originale
        $data = null;

        try {
            $data = $this->service->downloadAll($this->request,$resourceID);
        }
        catch(ResourceNotFoundException $e) {
            return $this->failNotFound($e->getMessage(), $e->getHttpCode());
        }
        catch(DownloadException | GenericException $e) {
            return $this->fail($e->getMessage(), $e->getHttpCode());
        }

        return $this->respond($data, 200, $this->messages['download_file']);

    }

    //---------------------------------------------------------------------------
    
}