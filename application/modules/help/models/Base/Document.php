<?php /* This file encoded by Raizlabs PHP Obfuscator http://www.raizlabs.com/software */ ?>
<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Help_Model_Document', 'con2');

/**
 * Help_Model_Base_Document
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $keyword
 * @property clob $content
 * @property string $answer
 * @property string $related_item
 * @property string $question
 * @property integer $module
 * @property integer $type_id
 * @property integer $category_id
 * @property Help_Model_Category $Category
 * @property Help_Model_Type $Type
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
abstract class Help_Model_Base_Document extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('document');
        $this->hasColumn('title', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('keyword', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('content', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('answer', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('related_item', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('question', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('module', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('type_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('category_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Help_Model_Category as Category', array(
             'local' => 'category_id',
             'foreign' => 'id'));

        $this->hasOne('Help_Model_Type as Type', array(
             'local' => 'type_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $blameable0 = new Doctrine_Template_Blameable();
        $versionable0 = new Doctrine_Template_Versionable(array(
             'versionColumn' => 'content',
             'className' => '%CLASS%Version',
             'auditLog' => true,
             ));
        $searchable0 = new Doctrine_Template_Searchable(array(
             'fields' => 
             array(
              0 => 'content',
              1 => 'title',
              2 => 'description',
              3 => 'keyword',
             ),
             ));
        $sluggable0 = new Doctrine_Template_Sluggable(array(
             'fields' => 
             array(
              0 => 'title',
             ),
             ));
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
        $this->actAs($blameable0);
        $this->actAs($versionable0);
        $this->actAs($searchable0);
        $this->actAs($sluggable0);
    }
}