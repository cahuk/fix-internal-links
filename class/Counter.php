<?php

class Counter
{

    /** @var int $_find_link counts how many references were found in the posts */
    private $_find_link = 0;

    /** @var int $_fixed_link counter fixed links */
    private $_fixed_link = 0;

    /** @var int counter appeal to Google */
    private $_query_google = 0;

    /** @var int counter how many times we got to the page with the CAPTCHA */
    private $_captcha_page = 0;

    /** @var int counter treated posts */
    private $_treated_posts = 0;

    /** @var int counter as anchors were added to the database */
    private $_add_anchor = 0;

    /** @var int posts with links to internal pages */
    private $_post_with_links = 0;


    /**
     * method increments the counter $this->_find_link
     */
    function incrementFindLink()
    {
        $this->_find_link ++;
    }

    /**
     * method increments the counter $this->_fixed_link
     */
    function incrementFixedLink()
    {
        $this->_fixed_link ++;
    }

    /**
     * method increments the counter $this->_query_google
     */
    function incrementQueryGoogle()
    {
        $this->_query_google ++;
    }

    /**
     * method increments the counter $this->_captcha_page
     */
    function incrementCaptchaPage()
    {
        $this->_captcha_page ++;
    }

    /**
     * method increments the counter $this->_treated_posts
     */
    public function incrementTreatedPosts()
    {
        $this->_treated_posts ++;
    }

    /**
     * method increments the counter $this->_add_anchor
     */
    public function incrementAddAnchor()
    {
        $this->_add_anchor ++;
    }

    /**
     * method increments the counter $this->_post_with_links
     */
    public function incrementPostWithLinks()
    {
        $this->_post_with_links ++;
    }


    /**
     * @return int count how many links is inoperative
     */
    public function getFindLink()
    {
        return $this->_find_link;
    }

    /**
     * @return int count fixed links
     */
    public function getFixedLink()
    {
        return $this->_fixed_link;
    }

    /**
     * @return int count appeals to Google
     */
    public function getQueryGoogle()
    {
        return $this->_query_google;
    }

    /**
     * @return int how many times a page of a CAPTCHA
     */
    public function getCaptchaPage()
    {
        return $this->_captcha_page;
    }

    /**
     * @return how many posts we passed
     */
    public function getTreatedPosts()
    {
        return $this->_treated_posts;
    }

    /**
     * @return int count as anchors were added to the database
     */
    public function getAddAnchor()
    {
        return $this->_add_anchor;
    }

    /**
     * @return int count posts which have links
     */
    public function getPostWithLinks()
    {
        return $this->_post_with_links;
    }


}