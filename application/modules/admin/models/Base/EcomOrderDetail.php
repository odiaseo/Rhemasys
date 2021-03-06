<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_EcomOrderDetail', 'admin');

/**
 * Admin_Model_Base_EcomOrderDetail
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $code
 * @property string $image_file
 * @property string $image_download
 * @property integer $is_virtual
 * @property string $payment_method
 * @property string $gift_message
 * @property integer $ecom_order_id
 * @property integer $ecom_product_id
 * @property decimal $price
 * @property decimal $discount
 * @property decimal $tax
 * @property integer $quantity
 * @property Admin_Model_EcomOrder $EcomOrder
 * @property Admin_Model_EcomProduct $EcomProduct
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_EcomOrderDetail extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('ecom_order_detail');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('code', 'string', 45, array(
             'type' => 'string',
             'length' => '45',
             ));
        $this->hasColumn('image_file', 'string', 150, array(
             'type' => 'string',
             'length' => '150',
             ));
        $this->hasColumn('image_download', 'string', 150, array(
             'type' => 'string',
             'length' => '150',
             ));
        $this->hasColumn('is_virtual', 'integer', 1, array(
             'type' => 'integer',
             'default' => 0,
             'length' => '1',
             ));
        $this->hasColumn('payment_method', 'string', 55, array(
             'type' => 'string',
             'length' => '55',
             ));
        $this->hasColumn('gift_message', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('ecom_order_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('ecom_product_id', 'integer', 4, array(
             'type' => 'integer',
             'default' => 0,
             'length' => '4',
             ));
        $this->hasColumn('price', 'decimal', null, array(
             'type' => 'decimal',
             'default' => 0,
             'length' => '',
             ));
        $this->hasColumn('discount', 'decimal', null, array(
             'type' => 'decimal',
             'default' => 0,
             'length' => '',
             ));
        $this->hasColumn('tax', 'decimal', null, array(
             'type' => 'decimal',
             'default' => 0,
             'length' => '',
             ));
        $this->hasColumn('quantity', 'integer', 4, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_EcomOrder as EcomOrder', array(
             'local' => 'ecom_order_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_EcomProduct as EcomProduct', array(
             'local' => 'ecom_product_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
    }
}