<?php

class QueryLog
{

    private $_db = null;

    private $_table_name = 'links_query_cache';


    function __construct( $db=null )
    {
        if( is_null( $db ) )
            throw new Exception("It does not define the class to work with the database");

        $this->_db = $db;
    }


    /**
     * @param $anchor string keyword (anchor)
     * @return mixed array|boolean relevant url or false
     */
    function  getLinkByAnchor( $anchor )
    {
        $relevant = $this->_db->fetchRow( " SELECT anchor, relevant_url FROM " .$this->_table_name. " WHERE anchor LIKE '" . $anchor . "' " );
        return $relevant;
    }


    /**
     * @param $link string url
     * @param $anchor string keyword (anchor)
     * @return mixed array|boolean relevant url or false
     */
    function  addLinkInlog( $relevant_url, $anchor )
    {
       $this->_db->insert( $this->_table_name, array( 'relevant_url'=>$relevant_url, 'anchor'=>$anchor ) );
    }

}