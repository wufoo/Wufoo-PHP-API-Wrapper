<?php

/**
 * Encapsulates the utility functions not required by user.
 *
 * @package default
 * @author Timothy S Sabat
 */
class WufooApiWrapperBase {

	protected function getHelper($url, $type, $iterator, $index = 'Hash') {
		$this->curl = new WufooCurl();
		$response = $this->curl->getAuthenticated($url, $this->apiKey);
		$response = json_decode($response);
		$className = 'Wufoo'.$type;
		$arr = array();
		foreach ($response->$iterator as $obj) {
			$arr[$obj->$index] = new $className($obj);
		}
		return $arr;
	}
	
	protected function getFullUrl($url) {
		return 'https://'.$this->subdomain.'.'.$this->domain.'/api/v3/'.$url.'.json';
	}
}


class WufooCurl {
	
	public function __construct() {
		//TIMTODO: set timout
	}
	
	public function getAuthenticated($url, $apiKey) {
		$this->curl = curl_init($url); 
		$this->setBasicCurlOptions();
		
		curl_setopt($this->curl, CURLOPT_USERPWD, $apiKey.':footastical');

        $response = $this->getResponse();
		$this->setResultCodes();
		$this->checkForCurlErrors();
		$this->checkForGetErrors($response);
		curl_close($this->curl);
		return $response;
	}
	
	public function postAuthenticated($postParams, $url, $apiKey) {
		$this->curl = curl_init($url); 
		$this->setBasicCurlOptions();
		
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-type: multipart/form-data', 'Expect:'));
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postParams);		
		curl_setopt($this->curl, CURLOPT_USERPWD, $apiKey.':footastical');

        $response = $this->getResponse();
		$this->setResultCodes();
		$this->checkForCurlErrors();
		$this->checkForPostErrors($response);
		curl_close($this->curl);
		return $response;
	}
	
	//http://stackoverflow.com/questions/2081894/handling-put-delete-arguments-in-php
	public function putAuthenticated($postParams, $url, $apiKey) {
		$this->curl = curl_init($url); 
		$this->setBasicCurlOptions();
		
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-type: multipart/form-data', 'Expect:'));
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($postParams));		
		curl_setopt($this->curl, CURLOPT_USERPWD, $apiKey.':footastical');

        $response = $this->getResponse();
		$this->setResultCodes();
		$this->checkForCurlErrors();
		$this->checkForPutErrors($response);
		curl_close($this->curl);
		return $response;
	}
	
	//http://stackoverflow.com/questions/2081894/handling-put-delete-arguments-in-php
	public function deleteAuthenticated($postParams, $url, $apiKey) {
		$this->curl = curl_init($url); 
		$this->setBasicCurlOptions();
		
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-type: multipart/form-data', 'Expect:'));
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($postParams));		
		curl_setopt($this->curl, CURLOPT_USERPWD, $apiKey.':footastical');

        $response = $this->getResponse();
		$this->setResultCodes();
		$this->checkForCurlErrors();
		//GET and DELETE both expect 200 response for success
		$this->checkForGetErrors($response);
		curl_close($this->curl);
		return $response;
	}
	
	public function setBasicCurlOptions() {
		//http://bugs.php.net/bug.php?id=47030
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl, CURLOPT_USERAGENT, 'Wufoo API Wrapper');
		curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

        if ($this->serverHasRestrictions()) {
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
        }
        else {
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($this->curl, CURLOPT_MAXREDIRS, 5);
        }
	}

    private function getResponse() {
        if ($this->serverHasRestrictions()){
            return $this->curl_exec_follow($this->curl);
        }
        return curl_exec($this->curl);
    }
	
	private function setResultCodes() {
		$this->ResultStatus = curl_getinfo($this->curl);		
	}

    private function serverHasRestrictions() {
        return (ini_get('open_basedir') != '' || ini_get('safe_mode' == 'On'));
    }
	
	private function checkForCurlErrors() {
		if(curl_errno($this->curl)) {
			throw new WufooException(curl_error($this->curl), curl_errno($this->curl));
		}
	}
	
	private function checkForGetErrors($response) {
		switch ($this->ResultStatus['http_code']) {
			case 200:
				//ignore, this is good.
				break;
			case 401:
				throw new WufooException('(401) Forbidden.  Check your API key.', 401);
				break;
			default:
				$this->throwResponseError($response);
				break;
		}
	}
	
	private function checkForPutErrors($response) {
		switch ($this->ResultStatus['http_code']) {
			case 201:
				//ignore, this is good.
				break;
			case 401:
				throw new WufooException('(401) Forbidden.  Check your API key.', 401);
				break;
			default:
				$this->throwResponseError($response);
				break;
		}
	}
	
	private function checkForPostErrors($response) {
		switch ($this->ResultStatus['http_code']) {
			case 200:
			case 201:
				//ignore, this is good.
				break;
			case 401:
				throw new WufooException('(401) Forbidden. Check your API key.', 401);
				break;
			default:
				$this->throwResponseError($response);
				break;
		}
	}
	
	private function throwResponseError($response) {
		if ($response) {
			$obj = json_decode($response);
			throw new WufooException('('.$obj->HTTPCode.') '.$obj->Text, $this->ResultStatus['HTTP_CODE']);
		} else {
			throw new WufooException('(500) This is embarrassing... We did not anticipate this error type.  Please contact support here: support@wufoo.com', 500);
		}
		return $response;
	}

    // Modified version of function from http://php.net/manual/en/function.curl-setopt.php#102121
    private function curl_exec_follow(/*resource*/ $ch, /*int*/ &$maxredirect = null) {
        $mr = $maxredirect === null ? 5 : intval($maxredirect);
        if ($mr > 0) {
            $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            $rch = curl_copy_handle($ch);
            curl_setopt($rch, CURLOPT_HEADER, true);
            curl_setopt($rch, CURLOPT_NOBODY, true);
            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
            curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
            do {
                curl_setopt($rch, CURLOPT_URL, $newurl);
                $header = curl_exec($rch);
                if (curl_errno($rch)) {
                    $code = 0;
                } else {
                    $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                    if ($code == 301 || $code == 302) {
                        preg_match('/Location:(.*?)\n/', $header, $matches);
                        $newurl = trim(array_pop($matches));
                    } else {
                        $code = 0;
                    }
                }
            } while ($code && --$mr);
            curl_close($rch);
            if (!$mr) {
                if ($maxredirect === null) {
                    trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
                } else {
                    $maxredirect = 0;
                }
                return false;
            }
            curl_setopt($ch, CURLOPT_URL, $newurl);
        }
        return curl_exec($ch);
    }
}

/**
 * Thrown by WufooCurl calls.
 *
 * @package default
 * @author Timothy S Sabat
 */
class WufooException extends Exception {
	
	public function __construct($message, $code) {
		parent::__construct($message, $code);
	}
	
};

?>