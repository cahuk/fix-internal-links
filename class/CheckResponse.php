<?php
/**
 * Class CheckResponse
 * It used to test the server response
 */

class CheckResponse
{

    /**
     * @param $url string link you want to check
     * @return bool if response is 200 return true, else false
     */
    public static function validResponse( $url )
    {
        $headers = @get_headers( $url );

        // check the server's response 200 - ОК
        if( strpos( $headers[0], '200' ) !== false )
            return true;

        return false;
    }


} 