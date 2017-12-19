<?php
/**
 *
 */
return array(
    'dbdriver'  =>  'mysqli',
    'pconnect'  =>  false,
    'dbcharset' =>  'utf8',
    'dbhost'    =>  '192.168.0.24',
    'dbuser'    =>  'root',
    'dbport'    =>  3306,
    'dbpw'      =>  '123456',
    'dbname'    =>  'messagedb',
    'tablepre'  =>  'ebh_',
    'slave'     =>  array(
        array(
            'dbhost'    =>  '192.168.0.24',
            'dbuser'    =>  'root',
            'dbport'    =>  3306,
            'dbpw'      =>  '123456',
            'dbname'    =>  'messagedb',
        )
    )
);