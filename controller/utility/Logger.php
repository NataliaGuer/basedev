<?php

namespace controller\utility;

/**
 * La classe logger è pensata per la gestione dei file di log prodotti dalle classi del progetto
 * durante la normale ed erronea esecuzione.  
 * Viene costruita passando l'istanza della classe che vuole scrivere un file di log per automatizzare
 * l'ottenimento della cartella in cui verrà scritto il log in questione;  
 * Ogni log fatto dalla classe rdb/env/esempio/classe.php
 * verrà scritto in rdb/log/env/esempio/classe/{date}.log e conterrà il trace dell'errore e i parametri passati al metodo andato in errore
 */
class Logger
{
    public function __construct($object)
    {
    }
}
