<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_BlogComment', 'admin');

/**
 * Admin_Model_Base_BlogComment
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $name
 * @property string $url
 * @property string $comment
 * @property integer $blog_post_id
 * @property integer $author
 * @property integer $is_active
 * @property decimal $rating
 * @property string $ip_address
 * @property Admin_Model_User $User
 * @property Doctrine_Collection $BlogPost
 * @property Doctrine_Collection $PostComments
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_BlogComment extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('blog_comment');
        $this->hasColumn('title', 'string', 55, array(
             'type' => 'string',
             'length' => '55',
             ));
        $this->hasColumn('name', 'string', 150, array(
             'type' => 'string',
             'length' => '150',
             ));
        $this->hasColumn('url', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             ));
        $this->hasColumn('comment', 'string', 1280, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '1280',
             ));
        $this->hasColumn('blog_post_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('user_id as author', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('is_active', 'integer', 1, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '1',
             ));
        $this->hasColumn('rating', 'decimal', 4, array(
             'type' => 'decimal',
             'length' => '4',
             ));
        $this->hasColumn('ip_address', 'string', 15, array(
             'type' => 'string',
             'ip' => true,
             'length' => '15',
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

        $this->hasMany('Admin_Model_BlogPost as BlogPost', array(
             'refClass' => 'Admin_Model_BlogPostComment',
             'local' => 'blog_comment_id',
             'foreign' => 'blog_post_id'));

        $this->hasMany('Admin_Model_BlogPostComment as PostComments', array(
             'local' => 'id',
             'foreign' => 'blog_comment_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
    }
}