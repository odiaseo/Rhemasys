<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_AdminSubsite', 'remote');

/**
 * Admin_Model_Base_AdminSubsite
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $keyword
 * @property string $contact_email
 * @property string $sales_email
 * @property string $telephone
 * @property string $fax
 * @property string $domain
 * @property string $root_dir
 * @property integer $user_id
 * @property integer $template_id
 * @property integer $ssid
 * @property integer $is_active
 * @property timestamp $renewal_at
 * @property string $colour_scheme
 * @property integer $address_book_id
 * @property Admin_Model_User $User
 * @property Admin_Model_AddressBook $AddressBook
 * @property Admin_Model_Template $Template
 * @property Doctrine_Collection $AdminLicence
 * @property Doctrine_Collection $Subsites
 * @property Doctrine_Collection $Users
 * @property Doctrine_Collection $SubsiteUsers
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_AdminSubsite extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('admin_subsite');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('keyword', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('contact_email', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'email' => true,
             'length' => '45',
             ));
        $this->hasColumn('sales_email', 'string', 45, array(
             'type' => 'string',
             'email' => true,
             'length' => '45',
             ));
        $this->hasColumn('telephone', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('fax', 'string', 45, array(
             'type' => 'string',
             'length' => '45',
             ));
        $this->hasColumn('domain', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('root_dir', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('user_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => true,
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('template_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => false,
             'length' => '4',
             ));
        $this->hasColumn('ssid', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => false,
             'default' => 1,
             'length' => '4',
             ));
        $this->hasColumn('is_active', 'integer', 1, array(
             'type' => 'integer',
             'default' => 0,
             'length' => '1',
             ));
        $this->hasColumn('renewal_at', 'timestamp', 25, array(
             'type' => 'timestamp',
             'length' => '25',
             ));
        $this->hasColumn('colour_scheme', 'string', 45, array(
             'type' => 'string',
             'default' => 'default',
             'length' => '45',
             ));
        $this->hasColumn('address_book_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => false,
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_User as User', array(
             'local' => 'user_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AddressBook as AddressBook', array(
             'local' => 'address_book_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_Template as Template', array(
             'local' => 'template_id',
             'foreign' => 'id'));

        $this->hasMany('Admin_Model_AdminLicence as AdminLicence', array(
             'refClass' => 'Admin_Model_AdminSubsiteLicence',
             'local' => 'admin_subsite_id',
             'foreign' => 'admin_licence_id'));

        $this->hasMany('Admin_Model_AdminSubsiteLicence as Subsites', array(
             'local' => 'id',
             'foreign' => 'admin_subsite_id'));

        $this->hasMany('Admin_Model_User as Users', array(
             'refClass' => 'Admin_Model_UserSubsite',
             'local' => 'admin_subsite_id',
             'foreign' => 'user_id'));

        $this->hasMany('Admin_Model_UserSubsite as SubsiteUsers', array(
             'local' => 'id',
             'foreign' => 'admin_subsite_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $blameable0 = new Doctrine_Template_Blameable();
        $this->actAs($timestampable0);
        $this->actAs($blameable0);
    }
}