<?php
namespace Console\Calculator;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Security {

	private $weight;

	private $value;

	private $totalAccountValue;


	/**
	 * Setter/getter for value
	 *
	 * @param string $val to set value
	 * @return string     to get value
	 */
	public function value($val = null) {
		if (!is_null($val)) {
			$this->value = $val;
		}

		return $this->value;
	}

	/**
	 * Setter/getter for total account value
	 *
	 * @param string $val to set value
	 * @return string     to get value
	 */
	public function totalAccountValue($val = null) {
		if (!is_null($val)) {
			$this->totalAccountValue = $val;
		}

		return $this->totalAccountValue;
	}

	/**
	 * Weight getter
	 *
	 * @return float security weight
	 */
	public function weight() {
		return $this->weight;
	}

	/**
	 * Calculates/sets wieght of a security in account.
	 *
	 * @return float weight
	 */
	public function calcWeight() {
		if (is_null($this->value) || $this->value <= 0) {
			throw new \Exception('Security must have some value');
		}

		if ($this->totalAccountValue() <= 0) {
			throw new \Exception('Account value must be greater than zero');
		}

 		$this->weight = round(($this->value() / $this->totalAccountValue()) * 100, 2);
	}

}