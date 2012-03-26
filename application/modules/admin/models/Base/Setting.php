<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_Setting', 'admin');

/**
 * Admin_Model_Base_Setting
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $code
 * @property string $param
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_Setting extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('setting');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 120, array(
             'type' => 'string',
             'length' => '120',
             ));
        $this->hasColumn('code', 'string', 80, array(
             'type' => 'string',
             'length' => '80',
             ));
        $this->hasColumn('param', 'string', null, array(
             'type' => 'string',
             ));


        $this->index('code_index', array(
             'fields' => 
             array(
              0 => 'code',
             ),
             'type' => 'unique',
             ));
        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
    }
}