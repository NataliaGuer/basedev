<?php

namespace model\entitySet;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;
use model\entity\BaseEntity;

class BaseEntitySet implements ArrayAccess, Iterator, Countable
{
    protected $entities = [];
    protected $i = 0;
    protected $entityType = BaseEntity::class;

    //aggiungere toJSON
    //aggiungere toArray

    /**
     * metodo per aggiunta di un'istanza di una classe che implementa BaseEntity
     * @param  BaseEntity $entity deve essere istanza di $entityType
     * @return void
     * @throws Exception se si prova a aggiungere un'entità non compatibile
     */
    public function add(BaseEntity $entity)
    {
        if($entity::class === $this->entityType){
            if (!array_key_exists($entity->getKey(), $this->entities)) {
                $this->entities[$entity->getKey()] = $entity;
            }
        } else {
            throw new Exception('Entity type mismatch');
        }
    }
    
    public function get($key)
    {
        $res = false;
        if (isset($this->entities[$key])) {
            $res = $this->entities[$key];
        }
        return $res;
    }
    
    //ArrayAccess    
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->entities) && isset($this->entities[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value::class === $this->entityType) {
            $this->add($value);
        } else {
            throw new Exception('Entity type mismatch');
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (array_key_exists($offset, $this->entities) && isset($this->entities[$offset])) {
            unset($this->entities[$offset]);
        }
    }

    //Iterator
    public function current(): mixed
    {
        $res = false;
        $values = array_values($this->entities);
        if ($values && array_key_exists($this->i, $values)) {
            $res = $values[$this->i];
        }
        return $res;
    }

    public function key(): mixed
    {
        return $this->i;
    }

    public function next(): void
    {
        $this->i++;
    }

    public function rewind(): void
    {
        $this->i = 0;
    }

    public function valid(): bool
    {
        $res = false;
        $values = array_values($this->entities);
        if ($values && array_key_exists($this->i, $values)) {
            $res = isset($values[$this->i]);
        }
        return $res;
    }

    //Countable
    public function count(): int
    {
        return count($this->entities);
    }
}
