<?php
/**
 * AddressServiceRest.class.php
 */

/**
 * Interface for the Avalara Address Web Service.
 *
 * AddressServiceRest reads its configuration values from parameters in the constructor
 *
 * <p>
 * <b>Example:</b>
 * <pre>
 *  $addressService = new AddressServiceRest("https://development.avalara.net","1100012345","1A2B3C4D5E6F7G8");
 * </pre>
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 *
 */

namespace AvaTax;

class AddressServiceRest
{
	static protected $classmap = array(
		'Validate' => 'Validate',
		'ValidateRequest' => 'ValidateRequest',
		'Address' => 'Address',
		'ValidAddress' => 'ValidAddress',
		'ValidateResult' => 'ValidateResult',
		'BaseResult' => 'BaseResult',
		'SeverityLevel' => 'SeverityLevel',
		'Message' => 'Message');

	protected $config = array();

	public function __construct($url, $account, $license)
	{
		$this->config = array(
			'url' => $url,
			'account' => $account,
			'license' => $license);
	}


	//Validates/normalizes a single provided address. Will either return a single, non-ambiguous validated address match or an error.
	public function validate($validateRequest)
	{
		if(!(filter_var($this->config['url'], FILTER_VALIDATE_URL))) {
			throw new AvaException("A valid service URL is required.", AvaException::MISSING_INFO);
		}

		if(empty($this->config['account'])){
			throw new AvaException("Account number or username is required.", AvaException::MISSING_INFO);
		}

		if(empty($this->config['license'])){
			throw new AvaException("License key or password is required.", AvaException::MISSING_INFO);
		}

		$url =  $this->config['url'].'/1.0/address/validate?'. http_build_query($validateRequest->getAddress());
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //Some Windows users have had trouble with our SSL Certificates. Uncomment this line to NOT use SSL.
		curl_setopt($curl, CURLOPT_USERPWD, $this->config['account'].":".$this->config['license']);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		if($error_number = curl_errno($curl)) {
			$error_msg = curl_strerror($error_number);
			throw new AvaException("AddressServiceRest cURL error ({$error_number}): {$error_msg}", AvaException::CURL_ERROR);
		}

		if(!$result) {
			throw new AvaException('AddressServiceRest received empty result from API', AvaException::INVALID_API_RESPONSE);
		}

		return ValidateResult::parseResult($result);

	}
}
?>
