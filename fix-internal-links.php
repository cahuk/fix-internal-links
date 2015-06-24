<?php 
/**
 *  Copyright (C) 2015  Alexander Cholovsky (email : webcahuk@gmail.com)
 *	The program finds broken links that refer to internal pages on the
 *  Anchor is the most relevant page according to Google and replacing her battered link
 *
 *  @author s1lent <webcahuk@gmail.com>
 *
 */

/** remove the time limit of the script */
set_time_limit (0);

/** autoloader for class */
require_once 'autoload.php';

/**
 * Index class from run application
 */
class Main
{
    /** The host with which to work */
    const HOST = 'yourdomain.com';

    /** sleep time for next iteration 10 seconds */
    const DELAY_DEFAULT = 10;

    /** interval to which the delay time is increased. 2 hours */
    const DELAY_INTERVAL = 7200;

    /** If you specify true, then the script can be run again with the first article */
    const DELETE_LOG_AFTER_COMPLETE = false;

    /** @var array $_exclude_substrings add path for filter. Links with entering these lines will not be processed */
    private $_exclude_substrings = array( '/page/', '/date/', '/author/' );
	
	/** @var array $_report_email add email for reports */
	private $_report_email = array( 'yours@email1' );	

    /** @var int $_sllep Time to delay */
    private $_sleepTime = 0;

    /** @var int $_max_id of posts default */
    private $_max_id = 1000;

	/** @var Db $_db_object link to Db object */
    private $_db_object = null;

	/** @var File $_file link to Db object */
    private $_file = null;

	/** @var Parsing $parsing link to Db object */
    private $_parsing = null;

	/** @var QueryLog $_query_log link to QueryLog object */
    private $_query_log = null;

	/** @var GoogleQuery $_google link to GoogleQuery object */
    private $_google = null;

	/** @var Counter $_counter link to Counter object */
    private $_counter = null;

    /** @var int $_start work script time (seconds) */
    private $_start = 0;

	
    /**
     * Constructor initialize all the necessary classes
     */
    function __construct()
    {

        $this->_start = microtime(true);
		
        $this->_db_object = Db::getInstance();

        $this->_file = new Files();

        $this->_google = new GoogleQuery();

        $this->_counter = new Counter();

        $this->_parsing = new Parsing( self::HOST );

        $this->_query_log = new QueryLog( $this->_db_object );

        /** @var int _sllepTime set the sleep Time to default */
        $this->resetSleepTime();
    }


    /**
     * method set the sleep Time
     * @param int $sllepTime
     */
    public function setSleepTime( $sllepTime )
    {
        $this->_sleepTime += $sllepTime;
    }
	

    /**
     * method set the sleep time to default
     */
    public function resetSleepTime( )
    {
        $this->_sleepTime = self::DELAY_DEFAULT;
    }
	

    /**
     * method starts the script
     */
    function run()
    {
        /** send this header if want debug with output */
        header( 'Content-Type: text/html; charset=utf-8' );

        $post = new Post( $this->_db_object );

        /** set max ID of posts */
        $this->_max_id = $post->getMaxId();

        /** @var int $current_post_id ID of post*/
        $current_post_id = 0;

        while ( $current_post_id <= $this->_max_id )
        {

            /** get last post id from logs */
            $current_post_id = $this->getCurrentPost( $post );

            /** count throws posts */
            $this->_counter->incrementTreatedPosts();

            /** get current_post */
            $current_post = $post->getPostById( $current_post_id );

            /** parse the content and obtain from them links */
            $links = $this->_parsing->parseContentLinks( $current_post[ Post::POST_FIELD_CONTENT ] );

            /** If there are no links in the article, then go to the next */
            if( ! empty( $links ) )
            {
                /** count post with links */
                $this->_counter->incrementPostWithLinks();

                foreach( $links as $link )
                {
                    /** @var string $local_link local link */
                    $local_link =  'http://' . self::HOST . '/' .  $link['url'];

                    /** if the server response is not 200 then you need to check whether there is a link in our database otherwise request from Google */
                    if( CheckResponse::validResponse( $local_link ) == false )
                    {
                        /** count find link */
                        $this->_counter->incrementFindLink();

                        /** @var string $ready_link relevant link for replace in post */
                        $ready_link = '';

                        /** check or not yet in the database of the anchor */
                        $relevant_log = $this->_query_log->getLinkByAnchor( $link['anchor'] );

                        /** If this anchor is not yet in our query logs, then turn to the relevant pages for Google */
                        if( ! $relevant_log )
                        {
                            /** @var boolean $page_valid if gogole result is valid page (not page with captcha) It must be true */
                            $page_valid = false;

                            while( $page_valid == false )
                            {
                                /** requested results from Google if reuslt is page with captcha we have to wait a while  */
                                $google_result = $this->_google->getResultByQuery( self::HOST, $link['anchor'] );

                                /** count Query google */
                                $this->_counter->incrementQueryGoogle();

                                /** check, or we got to the page with the issue, if it is (a page with captcha)
                                 * If you need to make the delay and retry after the passed interval $this->_sleepTime
                                 */
                                if( $this->_parsing->googlePageValid( $google_result ) )
                                {

                                    /** this is search issue set true */
                                    $page_valid = true;

                                    /** reset sleep time to default value (10 sec) */
                                    $this->resetSleepTime();

                                } else {
                                     /** increases the delay interval (sleep time) */
                                     $this->setSleepTime( self::DELAY_INTERVAL );

                                     /** count Captcha Page of google */
                                     $this->_counter->incrementCaptchaPage();

                                }

                                /** execute delay (sleep) before next query to google */
                                sleep( $this->_sleepTime );

                            }


                            /** parse links from the result obtained by Google */
                            $google_links = $this->_parsing->parseHostLinks( $google_result );

                            /** Getting links from the need to cut the ones that lead to the pagination / page /, by publication date / date / or author / author / */
                            if( $google_links )
                            {

                                /** We check all the links in the array, according to the template $ exclude substrings, if the entry is found, then remove the link from the array */
                                foreach( $google_links as $google_link  )
                                {

                                    /** применяем фильтры к url если они есть */
                                    if( empty( $this->_exclude_substrings ) // If the filter is not present, then simply enters all the results into an array
                                        OR
                                        (
                                          ! empty($this->_exclude_substrings)  // If there are filters and a link goes through these filters, then add it to the array
                                            AND
                                          $this->_parsing->linkFilters( $google_link[2], $this->_exclude_substrings ) == false
                                        )
                                      )
                                    {

                                        /** add link in the resulting array */
                                        $ready_link = $google_link[2];

                                        /** add anchor and link in DB */
                                        $this->_query_log->addLinkInlog( $google_link[2], $link['anchor'] );

                                        /** count add anchor in DB */
                                        $this->_counter->incrementAddAnchor();

                                    } // end if

                                } // end foreach

                            } // end if

                        }  else {

                            /** if  add link in the resulting array */
                            $ready_link = $relevant_log['relevant_url'];

                        } // end else

                        /**  replace the old link in the article to the new relevant */
                        if( ! empty( $ready_link ) )
                            $post_with_replase_link = $this->_parsing->replacePostLink( $current_post[ Post::POST_FIELD_CONTENT ],  $link['url'], $ready_link );

                        /** if replace correct update post content */
                        if( ! is_null( $post_with_replase_link ) && $post_with_replase_link != '' )
                        {
                            /** update post content by next iteration loop for links */
                            $current_post[ Post::POST_FIELD_CONTENT ] = $post_with_replase_link;

                            /** replace old link */
                            $post->updateContent( $current_post_id, $post_with_replase_link );

                            /** count fixed link */
                            $this->_counter->incrementFixedLink();

                        } // end if

                    } // end if

                } // end foreach $links

            } // end if

        } // end while



        /** delete the log files */
        if( self::DELETE_LOG_AFTER_COMPLETE == true )
            $this->_file->deleteLogFiles();


        /** Отправляем отчет */
        /** @var string $report_message forming report */
        $report_message = $this->getReportMessage();

        /** send report */
		if( ! empty( $this->_report_email ) ) 
		{
			$report = new Reports();
			$report->setMessage( $report_message );
			$report->send( $this->_report_email );
		}
		
    } // end run


    /**
     * @param $post Post object type Post
     * @return int $current_post post id
     */
    function getCurrentPost( $post )
    {
        /**
         * Check a file exists or the last post
         * ID if you have one, you need to take his name, ID is the last post, the program works with
         */
        $log_file_name = $this->_file->getLogFile();

        if( $log_file_name )
        {

            /** get the file name, and he also add the ID of the last post 1, which would take the next post */
            $current_post_id = $log_file_name + 1;

            /** Now you need to check or there is an article with this id, if not received the records on this Eid, then we increase it and try again */
            while( false === $post->getPostById( $log_file_name + 1 ) )
            {
                /** Insuring from the endless loop */
                if( $current_post_id > $this->_max_id )
                {
                    return $current_post_id;
                }

                $current_post_id++;

            }

            /** rename the log file under the current article */
            if( $current_post_id <= $this->_max_id )
                $this->_file->renameLogFile( $current_post_id );

        } else {

            /** @var int $current_post_id minimal id of posts */
            $current_post_id = $post->getMinId();

            /** create a file with the ID of the post */
            $this->_file->createLogFile( $current_post_id );
        }

        return $current_post_id;
    }

    /**
     * Method create message from report
     * @return string $report_message
     */
    function getReportMessage()
    {
        $report_message = "<p>За время выполнения скрипта было обработано: <strong>" . $this->_counter->getTreatedPosts() . "</strong> статей<br/>\n\r";
        $report_message .= "Из них: <strong>" . $this->_counter->getPostWithLinks() . "</strong> имеют ссылки на внутренние страницы сайта<br/>\n\r";
        $report_message .= "Неработоспособных (битых) ссылок найдено: <strong>" . $this->_counter->getFindLink() . "</strong> шт.<br/>\n\r";
        $report_message .= "Из них было исправлено: <strong>" . $this->_counter->getFixedLink() . "</strong> шт.<br/>\n\r";
        $report_message .= "За все время было выполнено: <strong>" . $this->_counter->getQueryGoogle() . "</strong> запросов в Google<br/>\n\r";
        $report_message .= "Из них мы попали на страницу с капчей: <strong>" . $this->_counter->getCaptchaPage() . "</strong><br/>\n\r";
        $report_message .= "В базу данных было записано: <strong>" . $this->_counter->getAddAnchor() . "</strong> анкоров с релевантными страницами</p><br/>\n\r";
        //$report_message .= "<p>Имя лог файла было сброшено на 0, теперь можно запусить скрипт повторно.<br/>\n\r";
        $report_message .= "<p>";
        $report_message .= sprintf( 'Время выполнения скрипта %2f сек.', microtime(true) - $this->_start ) . "<br/>\n\r";
        $report_message .= "Данные счетчика:<br/>\n\r";
        $report_message .= print_r( $this->_counter, true );
        $report_message .= "</p>";

        return $report_message;
    }

}


    /** application launch */
    $main = new Main();
    $main->run();