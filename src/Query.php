<?php

namespace AppsLine\MySQLMapper;

use AppsLine\MySQLMapper\Utils;

use AppsLine\MySQLMapper\Exception\ModelException;

class Query {

    private $schema;
    private $application;
    private $db_columns;
    private $db_table_name;
    private $db_table_alias;
    public $parent = [];
    private $number_instance;
    public $number_sub_relations = 0;
    private $action;
    private $fnActive;

    public $options;
    public $query;
    public $params;
    public $columns;
    public $values;
    public $alias_columns;

    function __construct($schema, $number_instance = 0, $attr_parent = null) {
        if(Utils :: isDefined($attr_parent))
            $this -> parent['attr'] = $attr_parent;

        $this -> schema = $schema;
        $this -> number_instance = $number_instance;
        $this -> application = implode('()->', array_reverse(array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 'function'))) . '()';
        $this -> db_columns = $this -> schema -> getDBColumns();
        $this -> db_table_name = $this -> schema -> getDBTableName();
        $this -> db_table_alias = 't'.$this -> number_instance.'_'.$this -> db_table_name;
    }

    public function insert($options = null) {
        $this -> action = 'insert';
        $this -> fnActive = 'insert';

        $this -> setOptions($options);
        $this -> setColumns();
        
        $this -> setValuesFromColumns();

        $this -> setTableToQueryInsert();
        $this -> setColumnsToQueryInsert();
        $this -> setValuesToQueryInsert();

        return [
            query => $this -> query,
            params => $this -> params
        ];
    }

    public function select($options = null) {
        $this -> action = 'select';
        $this -> fnActive = 'select';

        $this -> setOptions($options);
        $this -> setColumns();

        $this -> setValuesFromAttrs();

        $this -> setSelectToQuerySelect();
        $this -> setColumnsToQuerySelect();
        $this -> setTableToQuerySelect();

        $this -> setWhereToQuery();

        return [
            query => $this -> query,
            params => $this -> params,
            columns => [
                columns => $this -> columns,
                alias => $this -> alias_columns
            ]
        ];
    }

    public function selectAll($options = null) {
        $this -> action = 'select';
        $this -> fnActive = 'selectAll';

        $this -> setOptions($options);
        $this -> setColumns();

        $this -> setRelationsSchemas($this -> columns, $this);
        $this -> setValuesFromAttrs();

        $this -> setSelectToQuerySelect();
        $this -> setRelationsColumnsToQuerySelect();
        $this -> setTableToQuerySelect();
        $this -> setRelationsJoinsToQuerySelect();

        $this -> setWhereToQuery();
        
        return [
            query => $this -> query,
            params => $this -> params,
            columns => [
                columns => $this -> columns,
                alias => $this -> alias_columns
            ]
        ];
    }

    public function update($options = null) {
        $this -> action = 'update';
        $this -> fnActive = 'update';

        $this -> setOptions($options);
        $this -> setColumns();

        $this -> setValuesFromAttrs();

        $this -> setTableToQueryUpdate();
        $this -> setColumnsToQueryUpdate();
        
        $this -> setWhereToQuery();
        
        return [
            query => $this -> query,
            params => $this -> params
        ];
    }

    public function delete($options = null) {
        $this -> action = 'delete';
        $this -> fnActive = 'delete';

        $this -> setOptions($options);
        $this -> setColumns();

        $this -> setValuesFromAttrs();

        $this -> setTableToQueryDelete();

        $this -> setWhereToQuery();

        return [
            query => $this -> query,
            params => $this -> params
        ];
    }

    private function setOptions($options) {
        $this -> options = $options;

        if($this -> action === 'insert') {
            $this -> options['filter'] = true;
        }
        else if($this -> action === 'select') {
            if($this -> options['where']['check_instance'] === null) {
                $this -> options['where']['check_instance'] = true;
            }
        }
        else if($this -> action === 'update') {
            
        }
        else if($this -> action === 'delete') {
            
        }
    }

    private function setColumns() {
        if(is_array($this -> options['attr'])) {
            foreach ($this -> options['attr'] as $key => $column) {
                $attr = is_array($column) ? $key : $column;

                if(!$this -> attrExist($attr)) {
                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no existe', ModelException :: ATTR_NOT_EXIST, $attr);
                }
                $this -> columns[$attr] = $this -> db_columns[$attr];

                if(is_array($column)) {
                    if($column['render']) {
                        $this -> columns[$attr]['render'][$this -> action] = $column['render'];
                    }
                }
            }
        } else {
            if($this -> options['filter']) {
                foreach ($this -> db_columns as $key => $column) {
                    if($this -> containAttribute($key, 'auto_increment')) {
                        if($this -> schema -> attrIsDefined($key)) {
                            $this -> columns[$key] = $column;
                        }
                    }
                    else {
                        if($this -> containAttribute($key, 'not null')
                        || $this -> containAttribute($key, 'not empty')) {
                            $this -> columns[$key] = $column;
                        } else {
                            if($this -> schema -> attrIsDefined($key)) {
                                $this -> columns[$key] = $column;
                            }
                            else if($this -> containAttribute($key, 'default')) {
                                $this -> columns[$key] = $column;
                            }
                        }
                    }
                }
            }
            else {
                foreach ($this -> db_columns as $key => $column) {
                    if(!$this -> containAttribute($key, 'exclude')) {
                        $this -> columns[$key] = $column;
                    }
                }
            }
        }
    }

    private function setRelationsSchemas($columns, &$call_instance) {
        $is_relation = [];

        $this -> setColumnsToQuerySelect();
        $call_instance -> alias_columns = array_merge($call_instance -> alias_columns, $this -> alias_columns);
        $call_instance -> sub_relations[$this -> number_instance]['query']['columns'] = $this -> query;
        foreach ($columns as $attr => $column) {
            if($sub_schema = $this -> schema -> attrIsRelation($attr)) {
                if (is_subclass_of($sub_schema['schema'], '\AppsLine\MySQLMapper\CRUD')) {
                    if(Utils :: constExist($sub_schema['schema'], 'DB_MODEL')) {
                        $call_instance -> number_sub_relations++;
                        $class_query = new $this($sub_schema['schema'], $call_instance -> number_sub_relations, $this -> parent['attr'].$sub_schema['pathFull']);
    
                        if($this -> parent['type'] == 'LEFT' || !$this -> containAttribute($attr, 'not null') || !$this -> containAttribute($key, 'not empty')) {
                            $class_query -> parent['type'] = 'LEFT';
                        } else {
                            $class_query -> parent['type'] = 'INNER';
                        }
                        
                        $call_instance -> sub_relations[$class_query -> number_instance]['query']['relation'] =
                        $class_query -> parent['type'].' JOIN '.
                            $class_query -> db_table_name.' AS '.$class_query -> db_table_alias.
                            ' ON '.
                                $this -> db_table_alias.'.'.$column['name'].
                                    ' = '.
                                $class_query -> db_table_alias.'.'.$sub_schema['attr'];
    
                        $is_relation[] = $class_query;
                    }
                }
            }
        }

        foreach ($is_relation as $key => $class_query) {
            $class_query -> setColumns();
            $class_query -> setRelationsSchemas($class_query -> columns, $call_instance);
        }
    }

    private function setValuesFromColumns() {
        foreach ($this -> columns as $attr => $column) {
            if(is_array($this -> options['values']['attr']) && array_key_exists($attr, $this -> options['values']['attr'])) {
                $value = $this -> options['values']['attr'][$attr];
                $column['value'] = $this -> filterValue($attr, $value);
                $this -> values['columns'][$attr] = $column;
            }
            else {
                if($this -> schema -> attrIsDefined($attr)) {
                    $value = $this -> schema -> getValue($attr);
                    $column['value'] = $this -> filterValue($attr, $value);
                    $this -> values['columns'][$attr] = $column;
                }
                else if($this -> containAttribute($attr, 'default')) {
                    $value = $this -> getAttribute($attr, 'default');

                    if(is_string($value)) {
                        $value = $this -> getMapperRenderFunctions($this -> schema, $column, $value)['result'];
                    }

                    $column['value'] = $this -> filterValue($attr, $value);
                    $this -> values['columns'][$attr] = $column;
                } else {
                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no está instanciado', ModelException :: ATTR_NOT_INSTANCE, $attr);
                }
            }
        }
    }

    private function setValuesFromAttrs() {
        if(is_array($this -> options['where']['attr'])) {
            foreach ($this -> options['where']['attr'] as $key => $data) {
                if(is_numeric($key)) {
                    $attr = $data;
                    $column = $this -> db_columns[$attr];
                    if($this -> schema -> attrIsDefined($attr)) {
                        $value = $this -> schema -> getValue($attr);
                        $column['value'] = $this -> filterValue($attr, $value);
                        $this -> values['attrs'][$attr] = $column;
                    } else {
                        if($this -> options['where']['check_instance']) {
                            throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no está instanciado', ModelException :: ATTR_NOT_INSTANCE, $attr);
                        }
                    }
                }
                else {
                    $attr = $key;
                    $column = $this -> db_columns[$attr];
                    if(is_array($data)) {
                        if($data['render']) {
                            $column['render']['where'] = $data['render'];
                        }
                        if($this -> schema -> attrIsDefined($attr)) {
                            $value = $this -> schema -> getValue($attr);
                            $column['value'] = $this -> filterValue($attr, $value);
                            $this -> values['attrs'][$attr] = $column;
                        }
                        else if($data['value']) {
                            $column['value'] = $this -> filterValue($attr, $data['value']);
                            $this -> values['attrs'][$attr] = $column;
                        }
                        else if($this -> options['where']['check_instance']) {
                            throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no está instanciado', ModelException :: ATTR_NOT_INSTANCE, $attr);
                        }
                    } else {
                        $value = $data;
                        $column['value'] = $this -> filterValue($attr, $value);
                        $this -> values['attrs'][$attr] = $column;
                    }
                }
                if(!$this -> attrExist($attr)) {
                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no existe.', ModelException :: ATTR_NOT_EXIST, $attr);
                }
            }
        }
    }

    private function getMapperRenderFunctions($schema, $column, $text, $where = false) {
        $renderValue = false;
        $renderThis = false;

        if(strpos($text, '__DATETIME__') !== false) {
            $text = str_replace('__DATETIME__', date('Y-m-d H:i:s'), $text);
        }

        if(strpos($text, '__THIS__') !== false) {
            $renderThis = true;

            $text = str_replace('__THIS__', $this -> getColumnName($column['name']), $text);
        }

        if(strpos($text, '__VALUE__') !== false) {
            $renderValue = true;

            $text = str_replace('__VALUE__', $this -> convertToNameParam($column['name'], $where), $text);
        }

        if(preg_match_all('/(?<=__FUNCTION\()[a-zA-Z0-9_]+?(?=\)__)/', $text, $fn)) {
            $renderValue = true;

            $functions = $fn[0];

            if(is_array($functions)) {
                foreach ($functions as $key => $functionName) {
                    $functionFullName = '__FUNCTION(' . $functionName . ')__';
                    $value;

                    if(function_exists($functionName)) {
                        $value = $functionName($schema, $column);
                    } else if(method_exists($schema, $functionName)) {
                        $value = $schema -> $functionName($schema, $column);
                    } else {
                        throw new ModelException(get_class($schema), $this -> application, 'El metodo "'.$functionName.'" no existe', ModelException :: METHOD_NOT_EXIST, $functionName);
                    }
                    
                    $text = Utils :: strReplaceFirst($functionFullName, $value, $text);
                }
            }
        }

        if(preg_match_all('/(?<=__)[a-zA-Z0-9_]+?(?=__)/', $text, $variable)) {
            $renderValue = true;
            
            $variables = $variable[0];

            if(is_array($variables)) {
                foreach ($variables as $key => $variableName) {
                    $variableFullName = '__' . $variableName . '__';
                    $value;

                    if($schema -> attrIsDefined($variableName)) {
                        $value = $schema -> $variableName;
                    } else if(Utils :: constExist($schema, $variableName)) {
                        $value = Utils :: getConst($schema, $variableName);
                    }
                    
                    $text = Utils :: strReplaceFirst($variableFullName, $value, $text);
                }
            }
        }

        return [
            'result' => $text,
            'this' => $renderThis,
            'value' => $renderValue,
        ];
    }

    private function attrExist($attr) {
        if(!is_array($this -> db_columns[$attr])) {
            throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no existe', ModelException :: ATTR_NOT_EXIST, $attr);
        }
        return true;
    }

    private function filterValue($attr, $value) {
        $column = $this -> db_columns[$attr];

        if($value === null) {
            if($this -> containAttribute($attr, 'not null') 
                || $this -> containAttribute($attr, 'not empty')) {
                throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no puede tener un valor null', ModelException :: VALUE_NOT_BE_NULL, $attr);
            }
        } else if(gettype($value) === 'boolean') {
            switch ($this -> getType($attr)) {
                case 'string':
                case 's':
                case 'char':
                case 'varchar':
                case 'date':
                case 'datetime':
                case 'float':
                case 'f':
                case 'double':
                case 'd':
                case 'decimal':
                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" no puede ser un valor booleano', ModelException :: VALUE_NOT_BE_BOOLEAN, $attr);
                default:
                    break;
            }
        } else {
            if($column['sanitize']) {
                if(is_array($column['sanitize'])) {
                    foreach ($column['sanitize'] as $key => $sanitize) {
                        if(gettype($sanitize) === 'string') {
                            $sanitize = $this -> getMapperRenderFunctions($this -> schema, $column, $sanitize)['result'];

                            if(Utils :: isRegularExpression($sanitize) === true) {
                                $value = preg_replace($sanitize, '', $value);
                            }
                        }
                    }
                }
            }
    
            if($column['validate']) {
                if(is_array($column['validate'])) {
                    foreach ($column['validate'] as $key => $validate) {
                        if(gettype($validate) === 'string') {
                            $validate = $this -> getMapperRenderFunctions($this -> schema, $column, $validate)['result'];

                            if(Utils :: isRegularExpression($validate) === true) {
                                if(!preg_match($validate, $value)) {
                                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" contiene un valor no válido', ModelException :: VALUE_IS_INVALID, $attr);
                                }
                            } else if(!$validate) {
                                throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" contiene un valor no válido', ModelException :: VALUE_IS_INVALID, $attr);
                            }
                        }
                    }
                }
            }
    
            if($column['size']) {
                if(strlen($value) > $column['size']) {
                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" supera el tamaño máximo de "'.$column['size'].'" caracteres', ModelException :: VALUE_EXCEEDS_SIZE, $attr);
                }
            }
    
            if($this -> containAttribute($attr, 'not empty')) {
                if(gettype($value) === 'string') {
                    if(strlen(trim($value)) === 0) {
                        throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" contiene un valor vacío', ModelException :: VALUE_IS_EMPTY, $attr);
                    }
                }
            }

            if($this -> getType($attr) === 'datetime') {
                if(!preg_match('/(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))?/', $value)) {
                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" contiene una fecha no válida', ModelException :: VALUE_DATETIME_IS_INVALID, $attr);
                }
            } else if($this -> getType($attr) === 'date') {
                if(!preg_match('/(\d{4})-(\d{2})-(\d{2})/', $value)) {
                    throw new ModelException(get_class($this -> schema), $this -> application, 'El atributo "'.$attr.'" contiene una fecha no válida', ModelException :: VALUE_DATE_IS_INVALID, $attr);
                }
            }
        }


        return $value;
    }

    private function containAttribute($attr, $find) {
        $attributes = $this -> db_columns[$attr]['attributes'];
        if(is_array($attributes)) {
            if(in_array($find, $attributes, true)) {
                return true;
            }
            else if(array_key_exists($find, $attributes)){
                return true;
            }
            return false;
        }
        return false;
    }

    private function getAttribute($attr, $find) {
        $attributes = $this -> db_columns[$attr]['attributes'];
        if(is_array($attributes)) {
            if(array_key_exists($find, $attributes)){
                return $attributes[$find];
            }
            return false;
        }
        return false;
    }

    private function getType($attr) {
        return $this -> db_columns[$attr]['type'];
    }

    private function getColumnName($name) {
        if($this -> action != "delete") {
            $name = $this -> db_table_alias.'.'.$name;
        }
        return $name;
    }

    private function convertToNameParam($name, $where = false) {
        if($this -> action != "delete") {
            $name = ':'.$name;
        } else {
            $name = ':'.$this -> db_table_alias.'_'.$name;
        }
        if($where) $name .= '_where';

        return Utils :: cleanString($name);
    }

/*------------------------------------------------------------------------------
			Metodos de Concatenación
--------------------------------------------------------------------------------*/
		

    /*---------------------------------------
        Insert Table
    -----------------------------------------*/

    private function setTableToQueryInsert() {
        $this -> query = 'INSERT INTO '.$this -> db_table_name;
    }

    /*---------------------------------------
        Insert Columns
    -----------------------------------------*/
    private function setColumnsToQueryInsert() {
        $this -> query .= ' (';
        foreach ($this -> columns as $attr => $column) {
            $this -> query .= $column['name'];
                
            if($attr !== end(array_keys($this -> columns))) {
                $this -> query .= ', ';
            }
        }
        $this -> query .= ')';
    }

    /*---------------------------------------
        Insert Values
    -----------------------------------------*/
    private function setValuesToQueryInsert() {
        $this -> query .= ' VALUES (';
        foreach ($this -> values['columns'] as $attr => $column) {
            if(Utils :: isDefined($column['render'][$this -> action])) {
                $render = $this -> getMapperRenderFunctions($this -> schema, $column, $column['render'][$this -> action]);

                $this -> query .= $render['result'];

                if($render['value'] === true) {
                    $this -> params[] = [$this -> convertToNameParam($column['name']), $column['value'], $column['type']];
                }
            } else {
                $this -> query .= $this -> convertToNameParam($column['name']);
                $this -> params[] = [$this -> convertToNameParam($column['name']), $column['value'], $column['type']];
            }

            if($attr !== end(array_keys($this -> values['columns']))) {
                $this -> query .= ', ';
            }
        }
        $this -> query .= ');';
    }

    private function setSelectToQuerySelect() {
        $this -> query = 'SELECT ';
    }

    /*---------------------------------------
        Select Columns
    -----------------------------------------*/
    private function setColumnsToQuerySelect() {
        foreach ($this -> columns as $attr => $column) {
            $alias = $this -> db_table_alias.'_'.(Utils :: isDefined($column['label']) ? $column['label'] : $attr);

            if(Utils :: isDefined($column['render'][$this -> action])) {
                $render = $this -> getMapperRenderFunctions($this -> schema, $column, $column['render'][$this -> action]);

                $this -> query .= $render['result'];
            } else {
                $this -> query .= $this -> getColumnName($column['name']);
            }

            $this -> query .= ' AS '.$alias;
            $this -> alias_columns[$alias] = [
                'attr' => $this -> parent['attr'] . $attr,
                'is_relation' => $this -> schema -> attrIsRelation($attr) ? true : false
            ];
            
            if($attr !== end(array_keys($this -> columns))) {
                $this -> query .= ', ';
            }
        }
    }

    /*---------------------------------------
        Select Multiple relations Columns
    -----------------------------------------*/
    private function setRelationsColumnsToQuerySelect() {
        foreach ($this -> sub_relations as $key => $col) {
            $this -> query .= $col['query']['columns'];

            if($key !== end(array_keys($this -> sub_relations))) {
                $this -> query .= ', ';
            }
        }
    }

    /*---------------------------------------
        Select Multiple relations Joins
    -----------------------------------------*/
    private function setRelationsJoinsToQuerySelect() {
        foreach ($this -> sub_relations as $key => $col) {
            if($key !== reset(array_keys($this -> sub_relations))) {
                $this -> query .= ' '.$col['query']['relation'];
            }
        }
    }

    /*---------------------------------------
        Select Table
    -----------------------------------------*/
    private function setTableToQuerySelect() {
        $this -> query .= ' FROM '.$this -> db_table_name.' AS '.$this -> db_table_alias;
    }

    /*---------------------------------------
        Update Table
    -----------------------------------------*/
    private function setTableToQueryUpdate() {
        $this -> query = 'UPDATE '.$this -> db_table_name.' AS '.$this -> db_table_alias;
    }

    private function setColumnsToQueryUpdate() {
        $this -> query .= ' SET ';
        $this -> setValuesFromColumns();
        foreach ($this -> columns as $attr => $column) {
            if(Utils :: isDefined($column['render'][$this -> action])) {
                $render = $this -> getMapperRenderFunctions($this -> schema, $column, $column['render'][$this -> action]);

                $this -> query .= $this -> getColumnName($column['name']).' = ' .$render['result'];

                if($render['value'] === true) {
                    $this -> params[] = [$this -> convertToNameParam($column['name']), $this -> values['columns'][$attr]['value'], $column['type']];
                }
            } else {
                $this -> query .= $this -> getColumnName($column['name']).' = '.$this -> convertToNameParam($column['name']);
                $this -> params[] = [$this -> convertToNameParam($column['name']), $this -> values['columns'][$attr]['value'], $column['type']];
            }

            if($attr !== end(array_keys($this -> columns))) {
                $this -> query .= ', ';
            }
        }
    }

    /*---------------------------------------
        Delete Table
    -----------------------------------------*/
    private function setTableToQueryDelete() {
        $this -> query = 'DELETE FROM '.$this -> db_table_name;
    }

    /*---------------------------------------
        Select || Update || Delete Where
    -----------------------------------------*/
    private function setWhereToQuery() {
        if(is_array($this -> values['attrs'])) {
            $this -> query .= ' WHERE ';
            
            foreach ($this -> values['attrs'] as $attr => $column) {
                if(Utils :: isDefined($column['render']['where'])) {
                    $render = $this -> getMapperRenderFunctions($this -> schema, $column, $column['render']['where'], true);

                    $this -> query .= $this -> getColumnName($column['name']).' <=> '.$render['result'];

                    if($render['value'] === true) {
                        $this -> params[] = [$this -> convertToNameParam($column['name'], true), $column['value'], $column['type']];
                    }
                } else {
                    $this -> query .= $this -> getColumnName($column['name']).' <=> '.$this -> convertToNameParam($column['name'], true);
                    $this -> params[] = [$this -> convertToNameParam($column['name'], true), $column['value'], $column['type']];
                }

                if($column['value'] === '') {
                    $this -> query .= ' OR '.$this -> getColumnName($column['name']).' IS NULL';
                }

                if($attr !== end(array_keys($this -> values['attrs']))) {
                    $this -> query .= ' AND ';
                }
            }
        }
        
        $this -> query .= ';';
    }
}