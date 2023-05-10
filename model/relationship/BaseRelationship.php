<?php

namespace model\relationship;


/**
 * BaseRelationship
 * classe che rappresenta la relazione tra due tabelle
 * contiene i nomi delle tabelle su cui esiste la relazione da essa rappresentata e il nome delle colonne relative
 */
class BaseRelationship
{
    /**
     * parentTable
     *
     * The table in a foreign key relationship that holds the initial column values pointed to from the child table.  
     * The consequences of deleting, or updating rows in the parent table depend on the ON UPDATE and ON DELETE clauses in the foreign key definition.  
     * Rows with corresponding values in the child table could be automatically deleted or updated in turn,
     * or those columns could be set to NULL, or the operation could be prevented.
     * @var string
     */
    protected $parentTable;
    protected $parentTableColumn;
        
    /**
     * childTable
     *
     * In a foreign key relationship, a child table is one whose rows refer (or point) to rows in another table 
     * with an identical value for a specific column.  
     * This is the table that contains the FOREIGN KEY ... REFERENCES clause and optionally ON UPDATE and ON DELETE clauses.  
     * The corresponding row in the parent table must exist before the row can be created in the child table.  
     * The values in the child table can prevent delete or update operations on the parent table, 
     * or can cause automatic deletion or updates in the child table, 
     * based on the ON CASCADE option used when creating the foreign key.
     * @var string
     */
    protected $childTable;
    protected $childTableColumn;

    public function __get($name){
        return $this->$name;
    }
}
