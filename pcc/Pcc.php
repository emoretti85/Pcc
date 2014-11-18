<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
* This software consists of voluntary contributions made by many individuals
* and is licensed under the MIT license. For more information, see
* <http://www.doctrine-project.org>.
*/

/**
 * Pcc Class description
 *
 * This class allows you to convert monetary currencies through Yahoo web services (http://query.yahooapis.com/)
 *
 * PHP version 5
 * 
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author 	Ettore Moretti <ettoremoretti27{at}gmail{dot}com>
 * @copyright	Ettore Moretti 2014
 * @version	1.0
 * @since  	2014
 */

class Pcc {
	protected static $baseServiceUrl = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in";
	protected static $queryServiceUrl = "(%s)&env=store://datatables.org/alltableswithkeys";
	/**
	 * Convert static method
	 * 
	 * This method requires an input arrays consisting of n subarray containing 
	 * the currency FROM, TO and the value of the currency to be converted
	 * 
	 * Like this
	 * [
	 *	["EUR","USa",150.12],
	 *	["EUR","USD",10.10 ],
	 *	["USD","EUR",250.22],
	 *	["EUR","JPY",1350.42]
	 * ];
	 * 
	 * Returns an array with the same order as that passed in the input,
	 * with the addition of Rate, Date, Time and ConvertedValue
	 * 
	 * @param array $convert
	 * @return array $result
	 */
	static public function convert(array $convert) {
		$result = array ();
		$currencyKey = array ();
		$currencyQueryParameter = "";
		if (is_array ( $convert )) {
			foreach ( $convert as $k => $c ) {
				if (! in_array ( $c [0] . $c [1], $currencyKey )) {
					$currencyKey [] = $c [0] . $c [1];
					$currencyQueryParameter .= "\"" . $c [0] . $c [1] . "\",";
				}
			}
			$currencyQueryParameter = rtrim ( $currencyQueryParameter, "," );
		} else 
			return - 1;
		$url = self::$baseServiceUrl . sprintf ( self::$queryServiceUrl, $currencyQueryParameter );
		$serviceResult = simplexml_load_string ( self::webServiceCall ( $url ) );
		foreach ( $serviceResult->results->rate as $rateKey => $rate ) {
			foreach ( $convert as $k => $c ) {
				if (( string ) $rate ['id'] == $c [0] . $c [1]) {
					$result [$k] ['From'] = $c [0];
					$result [$k] ['To'] = $c [1];
					$result [$k] ['Value'] = $c [2];
					$result [$k] ['Rate'] = ( float ) $rate->Rate;
					$result [$k] ['Date'] = ( string ) $rate->Date;
					$result [$k] ['Time'] = ( string ) $rate->Time;
					$result [$k] ['ConvertedValue'] = $c [2] * ( float ) $rate->Rate;
				} else {
					$result [$k] ['From'] = $c [0];
					$result [$k] ['To'] = $c [1];
					$result [$k] ['Value'] = $c [2];
				}
			}
		}
		foreach ( $result as &$r ) {
			if (! isset ( $r ['ConvertedValue'] )) {
				$r ['Rate'] = "undefined";
				$r ['Date'] = "undefined";
				$r ['Time'] = "undefined";
				$r ['ConvertedValue'] = "undefined";
			}
		}
		return $result;
	}
	/**
	 * Sample private Curl call to the service
	 * 
	 * @param string $url
	 * @return mixed
	 */
	static private function webServiceCall($url) {
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_PROXY, "http://proxysogei.sogei.it" );
		curl_setopt ( $ch, CURLOPT_PROXYPORT, 8080 );
		curl_setopt ( $ch, CURLOPT_PROXYUSERPWD, "domus/emoretti:Password1" );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$return = curl_exec ( $ch );
		curl_close ( $ch );
		return $return;
	}
	?>
}
