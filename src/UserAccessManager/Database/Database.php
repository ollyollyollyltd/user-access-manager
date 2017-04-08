<?php
/**
 * Database.php
 *
 * The Database class file.
 *
 * PHP versions 5
 *
 * @author    Alexander Schneider <alexanderschneider85@gmail.com>
 * @copyright 2008-2017 Alexander Schneider
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @version   SVN: $Id$
 * @link      http://wordpress.org/extend/plugins/user-access-manager/
 */

namespace UserAccessManager\Database;

use UserAccessManager\Wrapper\Wordpress;

/**
 * Class Database
 *
 * @package UserAccessManager\Database
 */
class Database
{
    const USER_GROUP_TABLE_NAME = 'uam_accessgroups';
    const USER_GROUP_TO_OBJECT_TABLE_NAME = 'uam_accessgroup_to_object';

    /**
     * @var \wpdb
     */
    protected $WpDatabase;

    /**
     * @var Database
     */
    protected $Wordpress;

    /**
     * Database constructor.
     *
     * @param Wordpress $Wordpress
     */
    public function __construct(Wordpress $Wordpress)
    {
        $this->Wordpress = $Wordpress;
        $this->WpDatabase = $Wordpress->getDatabase();
    }

    /**
     * Returns the user group table name.
     *
     * @return string
     */
    public function getUserGroupTable()
    {
        return $this->WpDatabase->prefix.self::USER_GROUP_TABLE_NAME;
    }

    /**
     * Returns the user group table name.
     *
     * @return string
     */
    public function getUserGroupToObjectTable()
    {
        return $this->WpDatabase->prefix.self::USER_GROUP_TO_OBJECT_TABLE_NAME;
    }

    /**
     * @see dbDelta()
     *
     * @param string $mQueries
     * @param bool   $blExecute
     *
     * @return array
     */
    public function dbDelta($mQueries = '', $blExecute = true)
    {
        return $this->Wordpress->dbDelta($mQueries, $blExecute);
    }

    /**
     * @see \wpdb::$prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->WpDatabase->prefix;
    }

    /**
     * Returns the last insert id.
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->WpDatabase->insert_id;
    }

    /**
     * Returns the current blog id.
     *
     * @return int
     */
    public function getCurrentBlogId()
    {
        return $this->WpDatabase->blogid;
    }

    /**
     * Returns the blogs table name.
     *
     * @return string
     */
    public function getBlogsTable()
    {
        return $this->WpDatabase->blogs;
    }

    /**
     * Returns the posts table name.
     *
     * @return string
     */
    public function getPostsTable()
    {
        return $this->WpDatabase->posts;
    }

    /**
     * Returns the term_relationships table name.
     *
     * @return string
     */
    public function getTermRelationshipsTable()
    {
        return $this->WpDatabase->term_relationships;
    }

    /**
     * Returns the term_taxonomy table name.
     *
     * @return string
     */
    public function getTermTaxonomyTable()
    {
        return $this->WpDatabase->term_taxonomy;
    }

    /**
     * Returns the users table name.
     *
     * @return string
     */
    public function getUsersTable()
    {
        return $this->WpDatabase->users;
    }

    /**
     * Returns the capabilities table name.
     *
     * @return string
     */
    public function getCapabilitiesTable()
    {
        return $this->WpDatabase->prefix.'capabilities';
    }

    /**
     * @see \wpdb::get_col()
     *
     * @param string $sQuery
     * @param int    $iColumn
     *
     * @return array
     */
    public function getColumn($sQuery = null, $iColumn = 0)
    {
        return $this->WpDatabase->get_col($sQuery, $iColumn);
    }

    /**
     * @see \wpdb::get_row()
     *
     * @param string $sQuery
     * @param string $sOutput
     * @param int    $iRow
     *
     * @return array|null|object
     */
    public function getRow($sQuery = null, $sOutput = OBJECT, $iRow = 0)
    {
        return $this->WpDatabase->get_row($sQuery, $sOutput, $iRow);
    }

    /**
     * @see \wpdb::get_var()
     *
     * @param null $sQuery
     * @param int  $iColumn
     * @param int  $iRow
     *
     * @return null|string
     */
    public function getVariable($sQuery = null, $iColumn = 0, $iRow = 0)
    {
        return $this->WpDatabase->get_var($sQuery, $iColumn, $iRow);
    }

    /**
     * @see \wpdb::get_blog_prefix()
     *
     * @param int $iBlogId
     *
     * @return string
     */
    public function getBlogPrefix($iBlogId = null)
    {
        return $this->WpDatabase->get_blog_prefix($iBlogId);
    }

    /**
     * @see \wpdb::prepare()
     *
     * @param string $sQuery
     * @param mixed  $mArguments
     *
     * @return string
     */
    public function prepare($sQuery, $mArguments)
    {
        return $this->WpDatabase->prepare($sQuery, $mArguments);
    }

    /**
     * @see \wpdb::query()
     *
     * @param string $sQuery
     *
     * @return false|int
     */
    public function query($sQuery)
    {
        return $this->WpDatabase->query($sQuery);
    }

    /**
     * @see \wpdb::get_results()
     *
     * @param null   $sQuery
     * @param string $sOutput
     *
     * @return array|null|object
     */
    public function getResults($sQuery = null, $sOutput = OBJECT)
    {
        return $this->WpDatabase->get_results($sQuery, $sOutput);
    }

    /**
     * @see \wpdb::insert()
     *
     * @param string       $sTable
     * @param array        $aData
     * @param array|string $sFormat
     *
     * @return false|int
     */
    public function insert($sTable, array $aData, $sFormat = null)
    {
        return $this->WpDatabase->insert($sTable, $aData, $sFormat);
    }

    /**
     * @see \wpdb::update()
     *
     * @param string       $sTable
     * @param array        $aData
     * @param array        $aWhere
     * @param array|string $mFormat
     * @param array|string $mWhereFormat
     *
     * @return false|int
     */
    public function update($sTable, array $aData, array $aWhere, $mFormat = null, $mWhereFormat = null)
    {
        return $this->WpDatabase->update($sTable, $aData, $aWhere, $mFormat, $mWhereFormat);
    }

    /**
     * @see \wpdb::delete()
     *
     * @param string       $sTable
     * @param array        $aWhere
     * @param array|string $mWhereFormat
     *
     * @return false|int
     */
    public function delete($sTable, array $aWhere, $mWhereFormat = null)
    {
        return $this->WpDatabase->delete($sTable, $aWhere, $mWhereFormat);
    }

    /**
     * Returns the database charset.
     *
     * @return string
     */
    public function getCharset()
    {
        $sCharsetCollate = '';

        $sMySlqVersion = $this->getVariable('SELECT VERSION() as mysql_version');

        if (version_compare($sMySlqVersion, '4.1.0', '>=')) {
            if (!empty($this->WpDatabase->charset)) {
                $sCharsetCollate = "DEFAULT CHARACTER SET {$this->WpDatabase->charset}";
            }

            if (!empty($this->WpDatabase->collate)) {
                $sCharsetCollate .= " COLLATE {$this->WpDatabase->collate}";
            }
        }

        return $sCharsetCollate;
    }
}
