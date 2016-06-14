<?php

// THIS IS A THIRD-PARTY OUTPUT CACHING CLASS WRITTEN BY OUTCOURSING. WE MADE SMALL REVISIONS--COMMENTS IN ALL CAPS.

/*
 * Cache class that will cache any output from the output buffer to the hard disk. It's intended use is to be
 * a partial page caching class allowing multiple sections of the page to be cached and managed seperately. The
 * cache class can be implemented the following way:
 * $cache = new cache("homepage");
 * if($cache->exists == false) {
 *		$cache->start();
 *		[your output goes here]
 *		$cache->complete();
 *	}
 *	else {
 *		echo $cache->buffer;
 *	}
 */
class cache {
	/*
	 * PUBLIC MEMBERS
	 */

	/// exists is used to detect if the key is in cache.
	/// This is the flag that should be used to toggle your cached vs. dynamic content
	public $exists = false;

	/// buffer is the output saved in the cache file.
	/// This is the variable that contains what will be printed on the screen.
	public $buffer = '';

	/// hasError flag tells your code if there was an error executing your cache request
	public $hasError = false;


	/*
	 * PRIVATE MEMBERS - Cache Parameters. These parameters are used to configure the options
	 * in the cache class. These parameters are passed in to the constructor as an array.
	 * Example:
	 * 		$params = array("LIFE_TIME" => 1000);
	 *		$cache = new cache("homepage");
	 */
	private $LOAD_FROM_CACHE = 1; // Flag to turn on/off caching
	private $CACHE_REPOSITORY = ''; // Folder where the cache files are to be saved
	private $LIFE_TIME = 604800; // Cache duration. 1 WEEK. 86400 = 1 DAY

	// PRIVATE MEMBERS - Internal variables that are used by the class
	private $params = array(); // used to create unique key to create filename hash
	private $meta = array(); // internal array to store config values used for debugging
	private $stack = '########### Stack Trace ###########<br />'; //Stack trace is used to output debugging info
	private $status = null; // Status is used to output the last operation for debugging
	private $context = null; // Name of the instance to be saved. This should be unique per cache region
	private $file_name = null;

	/// Constructor
	/// $contentId = unique key to identify the region to be cached
	/// $params = array of configuration parameters ['LOAD_FROM_CACHE','CACHE_REPOSITORY', 'LIFE_TIME']
	/// NOTE: Params are not required as they all have default properties that can be set in the private members
	function __construct($contentId, $params = array()) {
		$this->context = $contentId;
		$this->params = implode(';',$params);
		//Set private variables
		foreach(array_keys($params) as $key){
			$this->$key = $params[$key];
		}

		// HARD-CODE OUTPUT DIRECTORY
		$this->CACHE_REPOSITORY = $GLOBALS['server_info']['physical_root'] . 'cache/';

		//Build meta array
		$this->LIFE_TIME = $this->LIFE_TIME+time();
		try {

			$serial = false;
			$name = $this->getUniqueName ($this->context,$this->params);

			$this->file_name = $name;

			if ($this->LOAD_FROM_CACHE == 1) {

				try {

					$serial = $this->read($name);

					if ($serial && $this->LOAD_FROM_CACHE == 1){
						$this->setMetaInfo();
						$data = $this->checkCacheData(unserialize($serial));

						$this->status = 'LOAD';

						if (! (bool) $data) {

							try {

								$this->status = 'REMOVE';
								$res = $this->remove($name);
								$this->stack .= "Cache entry expired<br />";
								return false;

							} catch (Exception $e) {

								$this->stack .= "Caught exception: ".$e->getMessage()."<br />";
								$this->hasError = true;
							}
						}
						$this->stack .= 'Cache data loaded<br />';
						$this->status = 'LOADED';
						$this->buffer = $data;
						$this->exists = !isset($GLOBALS['render_editable'])||!$GLOBALS['render_editable'];
						return $data;

					} else {
						$this->status = "NEW";
						$this->stack .= "Cache entry doesn't exist<br />";
						return false;
					}
				} catch (Exception $e) {

					$this->stack .= "Caught exception: ".$e->getMessage()."<br />";
					$this->hasError = true;
				}
			}

		} catch (Exception $e) {

			$this->stack .= "Caught exception: ".$e->getMessage()."<br />";
			$this->hasError = true;
		}

		return true;
	}

	/// Start method should be called to start the collection of the output buffer
	public function start() {
		ob_start();
	}

	/// Complete will grab the buffer contents and save them in a variable and to disk then flush the buffer.
	public function complete() {
		$this->buffer = ob_get_contents();
		$this->store($this->buffer);
		ob_end_flush();
	}

	/// STOP, BUT DON'T SAVE
	public function stop() {
		ob_end_flush();
	}

	/// Trace will output debug information. The $silent parameter is a flag that will output as a comment
	public function trace($silent = false) {
		$this->setMetaInfo();
		$date = date("F j, Y, g:i a");
		$str = ($silent) ? "<!-- STACK TRACE \n" : '';
		$str .= "<strong>($date) CACHE TRACE</strong><br />";


		foreach(array_keys($this->meta) as $key){
			$str .= "[".$key. "] => ".$this->meta[$key]. "<br />";
		}

		$str .= '<br />';

		if($silent) {
			$str = str_replace("<br />", "\n", $str);
			$str = str_replace("<strong>", "", $str);
			$str = str_replace("</strong>", "", $str);
			$str .= " -->";
		}

		return $str;
	}


	/// remove function will delete the cache file from the harddisk
	function remove ($file = '') {
		$this->stack .= "Attempting to remove expired cached entry.";

		// DEFAULT TO CURRENT FILE
		if($file=='') $file = $this->file_name;

		if (($result = unlink ( $file )) == false) {

			$this->stack .= 'Error reading from disk: Unable to remove expired file.<br />';
			return false;
		}
		return true;
	}

	/// save function will save data to a file on the hard disk
	private function save ($file, $data) {
		if(isset($GLOBALS['render_editable'])&&$GLOBALS['render_editable']) return;
		try {
			if (!file_exists($file)) {

				$cache = serialize($data);

				if (($fp = @fopen($file, 'w')) === false) {
					if (!is_dir(dirname($file))) {

						$this->hasError = true;
						$this->stack .= 'Error writing on disk: CACHE_REPOSITORY not exists.<br />';
						return false;
					}

					$this->hasError = true;
					$this->stack .= 'Error writing on disk: Unable to open file.<br />';
					return false;

				} else {
					if (!fwrite($fp, $cache)) {

						$this->hasError = true;
						$this->stack .= 'Error writing on disk:  Unable to write on  file.<br />';
						return false;
					}

					fclose($fp);
				}
			}
		}
		catch(Exception $e) {
			$this->hasError = true;
			$this->stack .= "Caught exception: " + $e.getMessage() + "<br />";
		}

		return true;
	}

	/// function that sets the meta data array that is used in the stack trace output
	private function setMetaInfo () {
		$this->meta = array('LIFE_TIME' => $this->LIFE_TIME,
			'CONTEXT' => $this->context,
			'CACHE_REPOSITORY' => $this->CACHE_REPOSITORY,
			'STATUS' => $this->status,
			'EXISTS' => $this->exists,
			'STACK' => $this->stack,
			'HASERROR' => $this->hasError
			//'BUFFER' => $this->buffer
			);

	}

	/// function that generates the unique filename for the cache file
	private function getUniqueName ($method, $params) {
		return $this->CACHE_REPOSITORY . 'cache_' . md5("{$method}");
	}


	/// function that will read the cache file from the cache file
	private function read ($file) {
		$serial = '';
		$this->stack .= "Reading file ".$file."<br />";

		if (file_exists($file)) {

			if (($fp = fopen($file, 'r')) === false){
				$this->stack .= 'Error reading from disk: Unable to open file.<br />';
				return false;

			} else {
				if (($serial = fread($fp, filesize($file))) === false) {

					$this->stack .= 'Error reading from disk: Unable to read file<br />';
					return false;
				}

				$this->stack .= "Cache data read from file.<br />";

				fclose($fp);
			}

		} else {

		    $serial = false;
		}
		return $serial;
	}

	/// method to prepare the data to be stored to the disk
	private function store ($data){
		$this->stack .= 'Attempting to store cache data.<br />';
		$this->hasError = false;

		try {

			$result = false;
			$name = $this->getUniqueName ($this->context,$this->params);

			// ADD TO LIST OF CACHE FILES ON CURRENT PAGE
			$GLOBALS['cache_files'][] = substr($name,strlen($this->CACHE_REPOSITORY));

			$this->status = 'STORE';

			if ($this->LOAD_FROM_CACHE == 1) {

				try {
					$this->setMetaInfo();
					$result = $this->save ($name, array('META' =>$this->meta, 'DATA' => $data));
					$this->stack .= "Cache data saved.<br />";

					return (bool) $result;

				} catch (Exception $e) {

					$this->hasError = true;
					$this->stack .= 'Caught exception: '.  $e->getMessage(). "<br />";
				}
			}

		} catch (Exception $e) {

			$this->hasError = true;
			$this->stack .= 'Caught exception: '.  $e->getMessage(). "<br />";

		}
	}

	/// method to verify the cached data
	private function checkCacheData ($serial) {
		$arrayobject = new ArrayObject($serial['META']);
		$lifetime_back = $this->LIFE_TIME;

		for ($iterator = $arrayobject->getIterator(); $iterator->valid(); $iterator->next()) {
			$key = $iterator->key();
			$this->$key = $iterator->current();
		}

		if ($this->LIFE_TIME >= time()) {

			return $serial['DATA'];
		}
		else {
			$this->LIFE_TIME = $lifetime_back;
		}
		return false;
	}
}

?>