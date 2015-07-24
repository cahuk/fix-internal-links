<?php

/**
 * Class for working with records (posts)
 */

class Post
{
    const POST_ID_FIELD_NAME = 'ID';

    const POST_FIELD_CONTENT = 'post_content';

    private $_db = null;

    private $_tableName = 'wp_posts';

    /** @var array $_selectFields some data fields you want to retrieve */
    private $_selectFields = array(
                                'ID',
                                'post_content',
                            );

    function __construct( $db=null )
    {
        if( is_null( $db ) )
            throw new Exception("It does not define the class to work with the database");

        $this->_db = $db;
    }


    function updateContent( $post_id, $post_content )
    {
        $this->_db->update( $this->_tableName, array( self::POST_FIELD_CONTENT => $post_content ), self::POST_ID_FIELD_NAME . " = ". $post_id );
    }

    /**
     * Method select min id for post
     * @return int $post_id
     */
    function getMinId()
    {
        $post_id = (int) $this->_db->fetchOne( " SELECT MIN( " . self::POST_ID_FIELD_NAME . " ) FROM $this->_tableName WHERE post_type='post' OR post_type='revision' " );
        return  $post_id;
    }


    /**
     * Method select min id for post
     * @return int $post_id
     */
    function getMaxId()
    {
        $post_id = (int) $this->_db->fetchOne( " SELECT MAX( " . self::POST_ID_FIELD_NAME . " ) FROM $this->_tableName WHERE post_type='post' OR post_type='revision' " );
        return  $post_id;
    }

    /**
     * method gets a data for post
     * @param $post_id
     * @return array $post_data data of post id
     */
    function getPostById( $post_id )
    {
       $post_data = $this->_db->fetchRow( " SELECT " . implode( ',' , $this->_selectFields ) . "
       FROM $this->_tableName WHERE " . self::POST_ID_FIELD_NAME . "= $post_id AND post_type='post' OR post_type='revision' " );
       return $post_data;
    }


} 