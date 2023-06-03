<?php

namespace controller\graphql;

use Exception;
use Throwable;

class Error extends Exception{
    //anagrafica dei codici di errore che potrebbero verificarsi
    //mapping codice errore -> messaggio
    //mantenimento trace
    //log

    const GRAPHQL_PARSE_FAILED = 0;

    protected $errorMessages = [
        self::GRAPHQL_PARSE_FAILED => 'Error while parsing query'
    ];

    protected $code;

    /**
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $code = 0, ?Throwable $previous = null){
        //controllare che il codice sia valido
        parent::__construct($this->errorMessages[$code], $code, $previous);
    }
}