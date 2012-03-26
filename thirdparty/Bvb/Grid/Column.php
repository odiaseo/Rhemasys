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
 * @version   $Id: Column.php 1538 2010-12-20 04:32:11Z bento.vilas.boas@gmail.com $
 * @link      http://zfdatagrid.com
 */

/**
 * This class is used to provide a deference mode to update grid columns.
 *
 * @author Bento Vilas Boas
 *
 */
class Bvb_Grid_Column
{

    public $_field;


    /**
     * @param string $field field from query
     *
     * @return void
     */
    public function __construct ($field)
    {
        $this->_field['field'] = trim($field);
    }


    /**
     * @param string $name field name
     * @param array $args array of options
     *
     * @return Bvb_Grid_Column
     */
    public function __call ($name, $args)
    {
        if ( substr(strtolower($name), 0, 3) == 'set' || substr(strtolower($name), 0, 3) == 'add' ) {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);
        }

        $this->_field[$name] = $args[0];
        return $this;
    }
}
