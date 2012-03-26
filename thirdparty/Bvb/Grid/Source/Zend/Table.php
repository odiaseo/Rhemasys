<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id: Table.php 1546 2011-01-07 18:49:40Z bento.vilas.boas@gmail.com $
 * @link      http://zfdatagrid.com
 */

class Bvb_Grid_Source_Zend_Table extends Bvb_Grid_Source_Zend_Select
{

    private $_model;


    public function getModel ()
    {
        return $this->_model;
    }


    public function buildForm ($inputsType = array())
    {
        $info = $this->getModel()->info();
        $cols = $info['metadata'];
        $form = $this->buildFormElements($cols, $info, $inputsType);
        return $form;
    }


    /**
     * Creating a query using a Model.
     * @param Zend_Db_Table_Abstract $model
     * @return $this
     */
    public function __construct (Zend_Db_Table_Abstract $model)
    {
        $this->_model = $model;
        $info = $model->info();
        $select = $model->select();

        $map = $info['referenceMap'];

        if ( is_array($map) && count($map) > 0 ) {

            $columnsToRemove = array();
            foreach ( $map as $sel ) {
                if ( is_array($sel['columns']) ) {
                    $columnsToRemove = array_merge($columnsToRemove, $sel['columns']);
                } else {
                    $columnsToRemove[] = $sel['columns'];
                }
            }

            $columnsMainTable = array_diff($info['cols'], $columnsToRemove);
            $select->from($info['name'], $columnsMainTable, $info['schema']);

            $this->_setJoins($info['name'], $map, $select);

        }else{
            $select->from($info['name'], $info['cols'], $info['schema']);
        }

        parent::__construct($select);

        return $this;
    }


    private function _setJoins ($tName, array $map, &$select, array &$tAlias = array())
    {
        foreach ( $map as $sel ) {

            $class = new $sel['refTableClass']();
            $info = $class->info();

            if ( ! isset($tAlias[$info['name']]) ) {
                $tAlias[$info['name']] = 0;
            }

            $alias = $tAlias[$info['name']] > 0 ? '_' . $tAlias[$info['name']] : null;

            if ( is_array($sel['columns']) ) {

                if ( ! is_array($sel['refColumns']) || (count($sel['columns']) != count($sel['refColumns'])) ) {
                    throw new Bvb_Grid_Exception('Mapping of ' . $sel['refTableClass'] . ' is wrong: columns and refColumns must have same type. In case of arrays, they must have same length.');
                }

                if ( ! array_key_exists('refBvbColumns', $sel) ) {
                    $cols = null;
                } else {
                    if ( ! is_array($sel['refBvbColumns']) ) {
                        $cols = array($sel['columns'][0] => $sel['refBvbColumns']);
                    } else {
                        $cols = $sel['refBvbColumns'];
                    }
                }

                $tFields = array_combine($sel['columns'], $sel['refColumns']);

                $join = null;
                foreach ( $tFields as $key => $value ) {
                    if ( ! is_null($join) ) {
                        $join .= ' AND ';
                    }
                    $join .= $info['name'] . $alias . '.' . $value . ' = ' . $tName . '.' . $key;
                }
                $select->joinLeft(array($info['name'] . $alias => $info['name']), $join, $cols, $info['schema']);
                $tAlias[$info['name']] ++;

            } else {
                if ( is_array($sel['refColumns']) ) {
                    throw new Bvb_Grid_Exception('Mapping of ' . $sel['refTableClass'] . ' is wrong: columns and refColumns must have same type.');
                }

                if ( array_key_exists('refBvbColumns', $sel) ) {
                    if ( is_array($sel['refBvbColumns']) ) {
                        $cols = $sel['refBvbColumns'];
                    } else {
                        $cols = array_combine((array) $sel['columns'], (array) $sel['refBvbColumns']);
                    }
                } else {
                    $cols = null;
                }
                $select->joinLeft(array($info['name'] . $alias => $info['name']), $info['name'] . $alias . '.' . array_shift($info['primary']) . ' = ' . $tName . '.' . $sel['columns'], $cols, $info['schema']);

            }

            $tAlias[$info['name']] ++;

            if ( ! array_key_exists('refBvbFollow', $sel) ) {
                $sel['refBvbFollow'] = false;
            }

            if ( is_array($info['referenceMap']) && count($info['referenceMap']) > 0 && $sel['refBvbFollow'] ) {
                $this->_setJoins($info['name'], $info['referenceMap'], $select, $tAlias);
            }
        }
    }


    public function getRecord ($table, array $condition)
    {


        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid_Model' . md5($this->buildWhereCondition($condition));
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $result = $this->getModel()->find($condition)->current();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = $this->getModel()->find($condition)->current();
        }


        if ( $result === null ) {
            return false;
        }

        return $result->toArray();
    }


    public function fetchDetail ( array $where)
    {
        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid_Model' . md5($this->buildWhereCondition($where));
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $result = $this->getModel()->find($where)->current();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = $this->getModel()->find($where)->current();
        }
        if ( $result === null ) {
            return false;
        }

        return $result->toArray();
    }


    public function delete ($table, array $condition)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }

        return $this->getModel()->find($condition)->current()->delete();
    }


    public function update ($table, array $post, array $condition)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }

        return $this->getModel()->fetchRow($condition)->setFromArray($post)->save();

    }


    public function insert ($table, array $post)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }

        return  $this->getModel()->createRow($post)->save();
    }


    /**
     * Get the primary table key
     * This is important because we only allow edit, add or remove records
     * From tables that have on primary key
     *
     * @return array
     */
    public function getIdentifierColumns ($table)
    {
        $info = $this->_model->info();

        $keys = array();
        foreach ( $info['primary'] as $pk ) {
            $keys[] = $info['metadata'][$pk]['TABLE_NAME'] . '.' . $pk;
        }

        return $keys;
    }

}