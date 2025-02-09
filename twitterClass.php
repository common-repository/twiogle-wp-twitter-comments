<?php


class TwitterSearch {
	/**
	 * Can be set to JSON (requires PHP 5.2 or the json pecl module) or XML - json|xml
	 * @var string
	 */
	var $type = 'json';
	
	/**
	 * It is unclear if Twitter header preferences are standardized, but I would suggest using them.
	 * More discussion at http://tinyurl.com/3xtx66
	 * @var array
	 */
	var $headers=array('X-Twitter-Client: PHPTwitterSearch','X-Twitter-Client-Version: 0.1','X-Twitter-Client-URL: http://ryanfaerman.com/twittersearch');
	
	/**
	 * Recommend setting a user-agent so Twitter knows how to contact you inc case of abuse. Include your email
	 * @var string
	 */
	var $user_agent='';
	
	/**
	 * @var string
	 */
	var $query='';
	
	/**
	 * @var array
	 */
	var $responseInfo=array();
	
	/**
	 * Use an ISO language code. en, de...
	 * @var string
	 */
	var $lang;
	
	/**
	 * The number of tweets to return per page, max 100
	 * @var int
	 */
	var $rpp=55;
	
	/**
	 * The page number to return, up to a max of roughly 1500 results
	 * @var int
	 */
	var $page;
	
	/**
	 * Return tweets with a status id greater than the since value
	 * @var int
	 */
	var $since;
	
	/**
	 * Returns tweets by users located within a given radius of the given latitude/longitude, where the user's location is taken from their Twitter profile. The parameter value is specified by "latitide,longitude,radius", where radius units must be specified as either "mi" (miles) or "km" (kilometers)
	 * @var string
	 */
	var $geocode;
	
	/**
	 * When "true", adds "<user>:" to the beginning of the tweet. This is useful for readers that do not display Atom's author field. The default is "false"
	 * @var boolean
	 */
	var $show_user = false;
	
	/**
	* @param string $query optional
	*/
	function TwitterSearch($query=false) {
		$this->query = $query;
	}
	
	/**
	* Find tweets from a user
	* @param string $user required
	* @return object
	*/
	function from($user) {
		$this->query .= ' from:'.str_replace('@', '', $user);
		return $this;
	}
	
	/**
	* Find tweets to a user
	* @param string $user required
	* @return object
	*/
	function to($user) {
		$this->query .= ' to:'.str_replace('@', '', $user);
		return $this;
	}
	
	/**
	* Find tweets referencing a user
	* @param string $user required
	* @return object
	*/
	function about($user) {
		$this->query .= ' @'.str_replace('@', '', $user);
		return $this;
	}
	
	/**
	* Find tweets containing a hashtag
	* @param string $user required
	* @return object
	*/
	function with($hashtag) {
		$this->query .= ' #'.str_replace('#', '', $hashtag);
		return $this;
	}
	
	/**
	* Find tweets containing a word
	* @param string $user required
	* @return object
	*/
	function contains($word) {
		$this->query .= ' '.$word;
		return $this;
	}
	
	/**
	* Set show_user to true
	* @return object
	*/
	function show_user() {
		$this->show_user = true;
		return $this;
	}
	
	/**
	* @param int $since_id required
	* @return object
	*/
	function since($since_id) {
		$this->since = $since_id;
		return $this;
	}
	
	/**
	* @param int $language required
	* @return object
	*/
	function lang($language) {
		$this->lang = $language;
		return $this;
	}
	
	/**
	* @param int $n required
	* @return object
	*/
	function rpp($n) {
		$this->rpp = $n;
		return $this;
	}
	
	/**
	* @param int $n required
	* @return object
	*/
	function page($n) {
		$this->page = $n;
		return $this;
	}
	
	/**
	* @param float $lat required. lattitude
	* @param float $long required. longitude
	* @param int $radius required. 
	* @param string optional. mi|km
	* @return object
	*/
	function geocode($lat, $long, $radius, $units='mi') {
		$this->geocode = $lat.','.$long.','.$radius.$units;
		return $this;
	}
	
	/**
	* Build and perform the query, return the results.
	* @param $reset_query boolean optional.
	* @return object
	*/
	function results($reset_query=true) {
		$request  = 'http://search.twitter.com/search.'.$this->type;
		$request .= '?q='.urlencode($this->query);
		
		if(isset($this->rpp)) {
			$request .= '&rpp='.$this->rpp;
		}
		
		if(isset($this->page)) {
			$request .= '&page='.$this->page;
		}
		
		if(isset($this->lang)) {
			$request .= '&lang='.$this->lang;
		}
		
		if(isset($this->since)) {
			$request .= '&since_id='.$this->since;
		}
		
		if($this->show_user) {
			$request .= '&show_user=true';
		}
		
		if(isset($this->geocode)) {
			$request .= '&geocode='.$this->geocode;
		}
		
		if($reset_query) {
			$this->query = '';
		}
		
		return $this->objectify($this->process($request))->results;
	}
	
	/**
	* Returns the top ten queries that are currently trending on Twitter.
	* @return object
	*/
	function trends() {
		$request  = 'http://search.twitter.com/trends.json';
		
		return $this->objectify($this->process($request));
	}
	
	/**
	 * Internal function where all the juicy curl fun takes place
	 * this should not be called by anything external unless you are
	 * doing something else completely then knock youself out.
	 * @access private
	 * @param string $url Required. API URL to request
	 * @param string $postargs Optional. Urlencoded query string to append to the $url
	 */
	function process($url, $postargs=false) {
		$ch = curl_init($url);
		if($postargs !== false) {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
        }
        
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $response = curl_exec($ch);
        
        $this->responseInfo=curl_getinfo($ch);
        curl_close($ch);
        
        if( intval( $this->responseInfo['http_code'] ) == 200 )
			return $response;    
        else
            return false;
	}
	
	/**
	 * Function to prepare data for return to client
	 * @access private
	 * @param string $data
	 */
	function objectify($data) {
		if( $this->type ==  'json' )
			return (object) json_decode($data);

		else if( $this->type == 'xml' ) {
			if( function_exists('simplexml_load_string') ) {
				$obj = simplexml_load_string( $data );

				$statuses = array();
				foreach( $obj->status as $status ) {
					$statuses[] = $status;
				}
				return (object) $statuses;
			}
			else {
				return $out;
			}
		}
		else
			return false;
	}
}
function toLink($text){
        $text = html_entity_decode($text);
        $text = " ".$text;
        $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',
                '<a href="\\1">\\1</a>', $text);
        $text = eregi_replace('(((f|ht){1}tps://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',
                '<a href="\\1">\\1</a>', $text);
        $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)',
        '\\1<a href="http://\\2">\\2</a>', $text);
        $text = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})',
        '<a href="mailto:\\1">\\1</a>', $text);
        return $text;
}
?>