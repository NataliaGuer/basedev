<?php

namespace controller\graphql\types;

class BaseType{

    /**
     * metodo che restituisce la rappresentazione sotto forma di array dell'oggetto  
     * se $fields è valorizzato, nella rappresentazione saranno contenute solo le proprietà il cui nome compare in esso
     * 
     * @var array|null $fields    contiene i nomi delle proprietà dell'oggetto che si desidera avere nell'array restituito
     * @return array
     */
    public function toArray(array $fields = null): array{
        $res = [];
        if ($fields) {
            foreach ($this as $key => $value) {
                if(in_array($key, $fields)){
                    if($value instanceof BaseType){
                        $res[$key] = $value->toArray($fields[$key]);
                    } else {
                        $res[$key] = $value;
                    }
                }
            }
        } else {
            foreach ($this as $key => $value) {
                if($value instanceof BaseType){
                    $res[$key] = $value->toArray();
                } else {
                    $res[$key] = $value;
                }
            }
        }
        return $res;
    }

}