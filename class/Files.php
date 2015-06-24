<?php

/**
 * Class for working with log files (recording and preimenovanie)
 * Log file is in papkk log, and it should be only one
 * The file is empty, but the file name is the ID of the last treated article
 * If proizodet fail, we will be able to see on what article it happened and start from the same place
 */

class Files
{

    private $folder = './log/';

    /**
     * returns the name of the log file or false
     */
    function getLogFile()
    {
        $log_file_name = 0;

        $files = $this->getFileFromLogDir();

        if( isset( $files[0] ) )
            $log_file_name = $files[0];

        return (int) $log_file_name;
    }

    /**
     * @param $post_id = file name alias and ID post
     */
    function createLogFile( $post_id )
    {
        $handle = fopen( $this->folder . $post_id, 'w' );

        fclose($handle);

        return $post_id;
    }


    /**
     * The method renames the old log file
     * @param $new_name_file string new post_id
     */
    function renameLogFile( $new_name_file )
    {
        $old_file_name = $this->getLogFile();

        $folder = $this->folder;

        rename( $folder . $old_file_name, $folder . $new_name_file );
    }


    /**
     * method delete a log files
     */
    function deleteLogFiles()
    {
        $files = $this->getFileFromLogDir();

        if( ! empty( $files ) )
        {
            foreach( $files as $file )
                unlink( dirname(__FILE__) . '/../' . $this->folder . '/' . $file);
        }
    }

    /**
     * @return array $log_files_name files from log dir
     */
    private function getFileFromLogDir()
    {
        $log_files_name = array();

        if ( $handle = opendir( dirname(__FILE__) . '/../' . $this->folder ) )
        {
            while ( false !== ($file = readdir($handle)) )
            {
                if( $file != '.' && $file != '..' )
                {
                    $log_files_name[] = $file;
                }
            }

            closedir( $handle );
        }

        return $log_files_name;
    }




} 