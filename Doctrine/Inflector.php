<?php

class Motnok_Doctrine_Inflector 
{
	
    public static function urlizeDanish($text)
    {
    	$text = str_replace(
    		array("æ", "ø", 'å'),
    		array("ae", "oe", "aa"),
    		$text
		);
		
		return Doctrine_Inflector::urlize($text);
    }	
	
}

