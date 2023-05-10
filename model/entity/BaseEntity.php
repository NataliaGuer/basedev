<?php

namespace model\entity;

//contiene la logica di base a tutte le entità
abstract class BaseEntity
{
    /** 
     * array che associa ai nomi delle proprietà della classe
     * i nomi delle colonne della tabella che le proprietà rappresentano
    */
    protected static $attributeToColumnMapper = [];

    /**
     * metodo che restituisce il valore della chiave primaria che identifica l'entità:  
     * - se la chiave primaria è costituita da una sola colonna viene restituito il
     * valore di quella colonna per l'entità  
     * - se la chiave primaria è costituita da due o più colonne viene restituiti i relativi valori per l'entità divisi 
     * da underscore ( es: tabella con chiave primaria (id, email) => viene restituito {$entity->id}_{$entity->email} )
     * 
     * @return string|int
     */
    abstract public function getKey();

    public static function getColumnName(string $propertyName)
    {
        if (array_key_exists($propertyName, static::$attributeToColumnMapper) && isset(static::$attributeToColumnMapper[$propertyName])) {
            return static::$attributeToColumnMapper[$propertyName];
        }
    }

    public function toArray()
    {
        //nome
    }

    /**
     * restituisce un array che associa al nome delle colonne della tabella il valore
     * degli attributi dell'entità che le rappresentano
     * @return array
     */
    public function map()
    {
        $map = [];
        foreach (static::$attributeToColumnMapper as $attributeName => $columnName) {
            if ($this->$attributeName !== null) {
                $map[$columnName] = $this->$attributeName;
            }
        }
        return $map;
    }

    //aggiungere toJSON
}
