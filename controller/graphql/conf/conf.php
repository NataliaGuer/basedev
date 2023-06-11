<?php

namespace controller\graphql\conf;

//classe che viene creata leggendo il file di configurazione
//un metodo per ogni operazione
//restituisce il nome completo della classe che gestisce la risoluzione dell'operazione e il nome del metodo della stessa classe che
//implementa direttamente la risoluziones

class conf {
    function getUser(){
        return [
            "controller\\services\\UserService",
            "getUser"
        ];
    }

    function getTest(){
        return [
            "controller\\services\\UserService",
            "getTest"
        ];
    }
}
