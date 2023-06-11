<?php

namespace controller\graphql;

use Exception;
use Throwable;

class Error extends Exception{

    const GRAPHQL_PARSE_FAILED = 'GRAPHQL_PARSE_FAILED';
    const GRAPHQL_RESOLUTION_FAILED = 'GRAPHQL_RESOLUTION_FAILED';
    const GRAPHQL_NO_SERVICE_FOR_OPERATION = 'GRAPHQL_NO_SERVICE_FOR_OPERATION';

    //usata per gestire gli errori non prevedibili e quindi non mappati 
    const INTERNAL_ERROR = 'INTERNAL_ERROR';

    protected $errorMessages = [
        self::GRAPHQL_PARSE_FAILED             => 'Error while parsing query',
        self::GRAPHQL_RESOLUTION_FAILED        => 'Error during query resolution',
        self::GRAPHQL_NO_SERVICE_FOR_OPERATION => 'Error during query resolution, no service for operation'
    ];

    protected string $errorCode;
    protected string $errorMessage;
    protected array $extraData;

    /**
     * @param int $code
     */
    public function __construct(string $code = null, ?Throwable $previous = null){
        if($code && in_array($code, $this->errorMessages)){
            $this->code = $code;
            $this->message = $this->errorMessages[$code];
        }
        //se viene sollevata una generica eccezione durante l'esecuzione si
        //registra come codice 'INTERNAL_ERROR' e come messaggio quello dell'eccezione precedente
        if (!$code && $previous !== null) {
            $this->errorCode = self::INTERNAL_ERROR;
            $this->errorMessage = $previous->getMessage();
        }
    }

    public function setExtraData(array $extraData){
        $this->extraData = $extraData;
    }

    public function getErrorCode(){
        return $this->errorCode;
    }

    public function getErrorMessage(){
        $errorMessage = $this->errorMessage;
        if($this->extraData){
            $extraDataString = json_encode($this->extraData);
            $errorMessage = "$errorMessage; info: $extraDataString";
        }
        return $errorMessage;
    }
}