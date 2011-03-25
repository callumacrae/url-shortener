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
	private $prepared;

	public function __construct(&$db = false, $table = false)
	{
		require_once('config.php');
		$this->config = $config;

		/**
		 * If no PDO object is sent, create one. It is preferred that if you
		 * already have a PDO object it should be sent. It will be used as
		 * a reference and will not be changed, but will speed up the
		 * construction of the shortener class as it won't have to create a
		 * new connection to the database.
		 */
		if ($db)
		{
			//$db can only be PDO, here we check for that
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
		
		/**
		 * Generate a prepared statement, so that if the get method is
		 * called more than one, the prepared statement is only called
		 * once, making the script faster.
		 */
		$this->prepared = $this->db->prepare('SELECT url FROM ' . $this->config['table'] . ' WHERE s_key = ?');
		
		if ($table)
		{
			$this->config['table'] = $table;
		}
	}
	
	/**
	 * Gets one or more values from the database. If $key is a number, it
	 * will get that many values and order them by $type, but if $key is the
	 * key, it will return the corresponding URL
	 *
	 * @param mixed $key The key to search for / the amount of rows to
	 * 	return
	 * @param string $type If $key is an int, order  by this
	 */
	public function get($key, $type = false)
	{
		if (is_int($key) && $type)
		{
			if (preg_match('/(?<item>.*):(?<value>.*)/', $type, $matches))
			{
				$statement = $this->db->prepare('SELECT * FROM ' . $this->config['table'] . ' WHERE ' . $matches['item'] . ' = ? LIMIT 0, ' . $key);
				$statement->execute(array($matches['value']));
				$statement = $statement->fetchAll(\PDO::FETCH_OBJ);
				return $statement;
			}
			else
			{
				$statement = $this->db->query('SELECT * FROM ' . $this->config['table'] . ' ORDER BY ' . $type . ' ASC LIMIT 0, ' . $key);
				$statement = $statement->fetchAll(\PDO::FETCH_OBJ);
				return $statement;
			}
		}
		else
		{
			//get single entry
			$statement = $this->prepared;
			$statement->execute(array($key));
			$statement = $statement->fetchObject();
			return $statement->url;
		}
	}
	
	/**
	 * Submit a URL into the database. Will return the key.
	 *
	 * @param string $url The URL to insert
	 */
	public function submit($url)
	{
		/**
		 * Primative regex check
		 */
		if (!preg_match('/^(?<proto>https?:\/{2})(?<domain>[a-zA-Z0-9\-.]+\.[a-zA-Z]{2,3})(?<path>\/\S*)?$/', $url, $matches))
		{
			$this->error = 'Invalid domain';
			return false;
		}
		
		/**
		 * Checking for an MX record will slow down the script. If you are
		 * finding the script too slow, disable this option using the 'mx'
		 * option in the configuration
		 */
		if ($config['mx'] && !checkdnsrr($matches['domain'], 'MX'))
		{
			$this->error = 'No MX record found for domain';
			return false;
		}
		
		/**
		 * If allowed, check whether the URL is already in the database. If
		 * it is, then just return the key for the old one as there is no
		 * point in creating a new one.
		 */
		if (!$this->config['unique'])
		{
			$statement = $this->db->prepare('SELECT s_key FROM ' . $this->config['table'] . ' WHERE url = ?');
			$statement->execute(array($url));
			if ($statement->rowCount())
			{
				$statement = $statement->fetchObject();
				return $statement->s_key;
			}
		}
		
		/**
		 * @todo Improve random string - there are other valid URL chars!
		 */
		$uniq_key = true;
		$statement = $this->db->prepare('SELECT id FROM ' . $this->config['table'] . ' WHERE s_key = ?');
		$key = substr(md5(uniqid(rand(), true)), 0, $this->config['length']);
		$statement->execute(array($key));

		//check whether the key exists. If it does, make a new one.
		while ($statement->rowCount() > 0)
		{
			$key = substr(md5(uniqid(rand(), true)), 0, $this->config['length']);
			$statement->execute(array($key));
		}
		
		//insert into database
		$statement = $this->db->prepare('INSERT INTO ' . $this->config['table'] . ' (s_key, url) VALUES (?, ?)');
		$statement->execute(array($key, $url));
		return $key;
	}
}
