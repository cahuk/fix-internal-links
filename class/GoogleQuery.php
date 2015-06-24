<?php

class GoogleQuery
{
    public $_curl = null;
    private $_host = null;

    function __construct()
    {
        $this->_curl = new Curl();
    }

    /**
     * Method send query to google search
     *
     * @param $query string anchor
     */
    function getResultByQuery( $host, $query )
    {
        $this->_host = trim($host);

        /** @var array $raw_query_array  an array of data broken down by a space request */
        $raw_query_array = explode( ' ', trim($query));

        $query_encode = array();

        if( $raw_query_array )
            foreach( $raw_query_array as $query )
                $query_encode[] = rawurlencode( $query );

        /** @var string $query_string return your search query in a row */
        $query_string = implode( '+', $query_encode );


        $google_url =  'https://www.google.com.ua/search?q=site:' . $this->_host . '+' .  $query_string;

        $result = $this->_curl->get( $google_url );

        return $result;

    }


} 