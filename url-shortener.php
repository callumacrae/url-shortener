<?php

/**
 * @package callumacrae's URL shortener library
 * @author Callum Macrae (http://lynxphp.com/)
 * @license Creative Commons Attribution-ShareAlike 3.0
 */

class Shortener
{
	private $db;
	private $config;

	public function __construct(&$db = false, $table = false)
	{
		require_once('config.php');
		$this->config = $config;

		if ($db)
		{
			if (!is_a($db, 'PDO'))
			{
				trigger_error('Must be PDO', E_USER_ERROR);
			}
			$this->db =& $db;
		}
		else
		{
			$dsn = 'mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['db'];
			$this->db = new PDO($dsn, $this->config['user'], $this->config['pass']);
		}
		
		if ($table)
		{
			$this->config['table'] = $table;
		}
	}
	
	public function get($key, $type = false)
	{
		if (is_int($key))
		{
			
		}
		else
		{
			$statement = $this->db->prepare('SELECT url FROM ' . $this->config['table'] . ' WHERE s_key = ?');
			$statement->execute(array($key));
			$statement = $statement->fetchObject();
			return $statement->url;
		}
	}
}
