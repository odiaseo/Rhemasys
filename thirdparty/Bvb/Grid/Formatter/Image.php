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
 * @version   $Id: Image.php 1462 2010-10-21 22:12:52Z bento.vilas.boas@gmail.com $
 * @link      http://zfdatagrid.com
 */

class Bvb_Grid_Formatter_Image implements Bvb_Grid_Formatter_FormatterInterface
{

    /**
     * Options to be aplied
     * @var array
     */
    protected $_options = array('border' => 0);


    /**
     * Constructor
     *
     * @param array $options
     *
     * @return void
     */
    public function __construct ($options = array())
    {
        $this->_options = $options;

        return;
    }


    /**
     * Formats a given value
     *
     * @see library/Bvb/Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
     */
    public function format ($value)
    {
        $attrs = '';
        if ( count($this->_options) > 0 ) {

            foreach ( $this->_options as $key => $value ) {
                $attrs .= " {$key} = \"$value\" ";
            }

        }

        return "<img  src=\"$value\" $attrs>";
    }
}