<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_EcomPersLayout', 'admin');

/**
 * Admin_Model_Base_EcomPersLayout
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property integer $ecom_category_id
 * @property integer $ecom_pers_item_id
 * @property integer $cols
 * @property integer $rows
 * @property integer $dpi
 * @property integer $overlays
 * @property decimal $height
 * @property decimal $width
 * @property decimal $margintop
 * @property decimal $marginright
 * @property decimal $marginbottom
 * @property decimal $marginleft
 * @property Admin_Model_EcomCategory $EcomCategory
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_EcomPersLayout extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('ecom_pers_layout');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'unique' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('ecom_category_id', 'integer', 4, array(
             'type' => 'integer',
             'default' => 0,
             'length' => '4',
             ));
        $this->hasColumn('ecom_pers_item_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('cols', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('rows', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('dpi', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('overlays', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('height', 'decimal', 18, array(
             'type' => 'decimal',
             'length' => '18',
             ));
        $this->hasColumn('width', 'decimal', 18, array(
             'type' => 'decimal',
             'length' => '18',
             ));
        $this->hasColumn('margintop', 'decimal', 18, array(
             'type' => 'decimal',
             'length' => '18',
             ));
        $this->hasColumn('marginright', 'decimal', 18, array(
             'type' => 'decimal',
             'length' => '18',
             ));
        $this->hasColumn('marginbottom', 'decimal', 18, array(
             'type' => 'decimal',
             'length' => '18',
             ));
        $this->hasColumn('marginleft', 'decimal', 18, array(
             'type' => 'decimal',
             'length' => '18',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_EcomCategory as EcomCategory', array(
             'local' => 'ecom_category_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
        $this->actAs($softdelete0);
    }
}