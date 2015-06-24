<?php

class Reports
{

    private $_message = "";


    private function getHeaders( )
    {
        $headers = "Content-type: text/html; charset=utf-8\r\n";
        return $headers;
    }


    private function getContent( )
    {
        $content  = "<html>\n";
            $content .= "<body>\n";
                $content .= $this->_message;
            $content .= "\n</body>\n";
        $content .= "</html>";

        return $content;

    }


    function setMessage( $message )
    {
        $this->_message = $message;

    }


    function send( $to=array(), $subject = "Отчет работы скрипта 'Исправление внутренней перелинковки'" )
    {

        mail( implode( ',', $to ), $subject, $this->getContent(), $this->getHeaders() );

    }


} 