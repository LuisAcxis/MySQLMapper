<?php

namespace AppsLine\MySQLMapper;

use AppsLine\MySQLMapper\Utils;
use AppsLine\MySQLMapper\Schema;

abstract class CRUD extends Schema {

    public function create() {
        $db = $this -> getDB();

        $result = $db -> insert();
        if($result) {
            return $db -> lastInsertId();
        }
    }
    
    public function list() {
        $db = $this -> getDB();

        $attrs = $this -> getDBAttrs();

        $result = $db -> select([
            'where' =>[
                'attr' => $attrs,
                'check_instance' => false
            ]
        ]);

        if($result) {
            return $result;
        }
    }
    
    public function update($id = null) {
        $db = $this -> getDB();

        $id = Utils :: isDefined($id) ? $id : $this -> id;
        
        if(!$this -> exist(['id' => $id])) {
            throw new Exception('Id no existe');
        }

        $result = $db -> update([
            'where' => [
                'attr' => [
                    'id' => $id
                ]
            ]
        ]);
        
        if($result) {
            return true;
        }
    }

    public function delete($id = null) {
        $db = $this -> getDB();

        $attrs = $this -> getDBAttrs();

        $result = $_web -> db -> delete([
            'where' =>[
                'attr' => $attrs,
                'check_instance' => false
            ]
        ]);

        if($result) {
            return $result;
        }
    }

    public function fetch($id = null) {
        $db = $this -> getDB();

        $id = Utils :: isDefined($id) ? $id : $this -> id;

        if(!$this -> exist(['id' => $id])) {
            throw new Exception('Id no existe');
        }

        $result = $db -> fetch([
            'where' => [
                'attr' =>  [
                    'id' => $id
                ]
            ]
        ]);

        if($result) {
            return $this;
        }
    }

    public function exist($attr = null) {
        $db = $this -> getDB();

        $attrs = Utils :: isDefined($attr) ? $attr : $this -> getDBAttrs();

        $result = $db -> select([
            'attr' => [
                'id'
            ],
            'where' => [
                'attr' => $attrs,
                'check_instance' => false
            ]
        ]);

        if($result) {
            if(is_array($result)) {
                return array_column($result, 'id');
            }
            return false;
        }
    }
}