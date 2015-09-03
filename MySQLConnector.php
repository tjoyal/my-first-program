<?php

/**
 * @file MySQLConnector.php
 *
 * Copyright (C) 2009 Thierry Joyal <thierry.joyal@gmail.com>
 *
 * This material was created and intended for presentation only
 */

require_once("DbConnector.php");


class MySQLConnector extends DbConnector
{
	protected function __construct($oConnection)
	{
		parent::__construct($oConnection, "MySQLConnector");
	}
	
	public static function getInstance($sServer, $sUser, $sPdw, $sDb, $bPersistant = true)
	{
		$oClassContext = NULL;
		
		if($bPersistant)
		{
			$oClassContext = self::getSavedInstance($sServer, $sUser, $sPdw, $sDb);
		}
			
		if(is_null($oClassContext))
		{
			$oConnection = mysql_connect($sServer, $sUser, $sPdw);
					
			if(!$oConnection)
			{
				$sError = sprintf(self::ERR_CONNECT, $sServer);

				self::errorHandler($sError);
				
				return NULL;
			}
			
			$bResult = mysql_select_db($sDb, $oConnection);
			
			if(!$bResult)
			{
				$sError = sprintf(self::ERR_DATABASE, $sDb);

				self::errorHandler($sError);
				
				return NULL;
			}
			
			$oClassContext = new self($oConnection);
			
			if($bPersistant)
			{
				self::setSavedInstance($sServer, $sUser, $sPdw, $sDb, $oClassContext);
			}	
		}
		
		return $oClassContext;
	} 
	
	public static function db_query($sSql, $oConnection)
	{
		$oResult = mysql_query($sSql, $oConnection);
		
		if($oResult == false)
		{	
			$sError = sprintf(self::ERR_QUERY);

			self::errorHandler($sError);
			
			return NULL;
		}
		
		return $oResult;
	}
	
	public static function db_fetch_object($oResult)
	{
		$oRow = mysql_fetch_object($oResult);
		
		return $oRow;
	}
	
	public static function db_fetch_array($oResult)
	{
		$arrRow = mysql_fetch_array($oResult);
		
		return $arrRow;
	}
	
	public static function db_free_result($oResult)
	{
		$bResult = mysql_free_result($oResult);
	
		if(!$bResult)
		{
			$sError = sprintf(self::ERR_RESULTSET);

			self::errorHandler($sError);
		} 
		
		return $bResult;
	}
	
	//Could be optimised to create on the fly reports but of course this should be in another library
	public static function db_dump_HTML($sSql, $oConnection)
	{
		$sHTML = "<hr />";
		$sHTML = "Query : " . $sSql;
		
		$oResult = self::db_query($sSql, $oConnection);
		
		$bFirstLine = true;
		
		echo("<table border=1>");
		
		while ($oRow = self::db_fetch_object($oResult))
		{
			if($bFirstLine)
			{
				echo("<tr>");
				foreach($oRow as $key => $data)
				{
					echo("<th><nobr>" . $key . "</nobr></th>");
				}
				echo("</tr>");
			
				$bFirstLine = false;
			}
			
			echo("<tr>");
			foreach($oRow as $key => $data)
			{
				if($data == "")
				{
					$data = " ";
				}
					
				echo("<td>" . $data . "</td>");
			}
			echo("</tr>");
		}
		
		echo("</table>");
	   
	    self::db_free_result($oResult);
	}
	
}


?>
