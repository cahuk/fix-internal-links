<?php

class Parsing
{

    /**
     * domain for which you want to make the internal linking, without www. and screening points \.
     */
    private $_domain = '';

    /**
     * @param string $host host name
     */
    function __construct( $host )
    {
        if( is_null($host) )
            throw new Exception("You must give the host name");
        /** shield the point in the domain name */
        $this->_domain = str_replace('.', '\.', $host );

    }


    /**
     * @param $post_content
     * @param $old_link
     * @param $new_link
     * @return string new post contemt
     */
    function replacePostLink( $post_content, $old_link, $new_link )
    {
        /** @var string $replace_patern  patern old link with host name*/
        $replace_patern = '/' . $this->getPatternForHost() . str_replace( '/', '\/', trim( $old_link , '/' ) ) . '\/?/';

        /** @var string $new_post_content replase old link to new_link */
        $new_post_content = preg_replace( $replace_patern, $new_link, $post_content );

        return $new_post_content;
    }


    /**
     * @return string $domain pattern for this domain or relative path of root
     */
    function getPatternForHost()
    {
        /** www substitution and the absolute and relative path links*/
        $domain =  '(https?:\/\/(www\.)?' . $this->_domain . '\/|\/)';
        return $domain;
    }

    /**
     * method returns the pattern to search for references in Post
     * @return string $final_pattern patter
     */
    function getContenLinkPattern()
    {
        $final_pattern = '/<a\s+href="' . $this->getPatternForHost() . '(.+?)".*?>(.+?)<\/a>/';
        return $final_pattern;
    }


    /**
     * Method parse content anf found links
     * @param $content str post content
     */
    function parseContentLinks( $content )
    {

        /** @var array $coontent_links raw data links */
        $coontent_links = array();
        preg_match_all( $this->getContenLinkPattern(), $content, $coontent_links, PREG_SET_ORDER );

        /** @var array $links processed data to return to the client code */
        $links = array();

        if( $coontent_links ) {
            foreach( $coontent_links as $item )
                $links[] = array( 'url'=>$item[3], 'anchor'=>$item[4] );
        }

        return $links;

    }

    /**
     * Method parse google content and find links for this host
     *
     * @param string $content content form google
     * @param boolean $host
     * @return array $coontent_links It consists of link $coontent_links[0][1]
     */
    function parseHostLinks( $content )
    {
        $pattern = '/<a\s+href="(https?:\/\/' . $this->_domain . '(.+?))".*>.+<\/a>/';

        $coontent_links = array();
        preg_match_all( $pattern, $content, $coontent_links, PREG_SET_ORDER);

        return $coontent_links;
    }


    /**
     * Method checks or string $link has of the substring of the array $substrings
     *
     * @param string $link
     * @param array $patterns
     * @return boolean true|false If $link is a substring of at least one array $substrings return true, else return false
     */
    function linkFilters( $link, Array $substrings )
    {
        foreach( $substrings as $str ) {
            if( strpos( $link, $str ) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method parse content that we have received from Google, and if this is not page with captcha return true. We consider one of the common page
     * @param string $google_result result page from google
     * @return int $result is valid page return true
     */
    function googlePageValid( $google_result )
    {
        $captcha_page_pattern = '/<form\s+.*action="CaptchaRedirect".*id="captcha"/i';

        $result = preg_match( $captcha_page_pattern, $google_result );

        /** if we found string for pattern so we not passed validation and this is page with captcha */
        return ( $result ? false : true ) ;
    }

} 