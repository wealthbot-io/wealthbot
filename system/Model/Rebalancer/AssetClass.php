<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class AssetClass extends Base {

	private $name;

    private $toleranceBand;

	private $targetPercent;

	private $currentPercent;

	private $oob;

	private $totalFunds;

	public function name($val = null)
    {
        if (null !== $val) {
			$this->name = $val;
		}

		return $this->name;
	}

    public function toleranceBand($val = null)
    {
        if (null !== $val) {
            $this->toleranceBand = $val;
        }

        if (null === $this->toleranceBand) {
            $data = $this->__getData();
            $this->toleranceBand = (isset($data['tolerance_band']) ? $data['tolerance_band'] : 0);
        }

        return $this->toleranceBand;
    }

	public function targetPercent($val = null)
    {
        if (null !== $val) {
			$this->targetPercent = $val;
		}

//		if (null === $this->targetPercent) {
//			$res = $this->__getCEModelData();
//			$this->targetPercent = isset($res[0]['percent']) ? $res[0]['percent'] : null;
//		}
		return $this->targetPercent;
	}


	public function currentPercent($val = null)
    {
        if (null !== $val) {
			$this->currentPercent = $val;
		}

		return $this->currentPercent;
	}

	public function totalFunds($val = null)
    {
		if (null !== $val) {
			$this->totalFunds = $val;
		}

		return $this->totalFunds;
	}

	public function calcOob()
    {
		return $this->oob = $this->currentPercent - $this->targetPercent;
	}

	public function rebalToleranceBands($val = null) {
		if (!is_null($val)) {
			$this->rebalToleranceBands = $val;
		}

		if (!isset($this->rebalToleranceBands) || is_null($this->rebalToleranceBands)) {
			$res = $this->__getData();
			$this->rebalToleranceBands = $res[0]['tolerance_band'];
		}

		return $this->rebalToleranceBands;
	}

	private function __getCEModelData() {
		$q = 'SELECT * FROM `ce_model_entities` WHERE `id` = ' . $this->id;
		$res = $this->db->q($q);
		return $res;
	}

	private function __getData() {
		$q = 'SELECT * FROM `asset_classes` WHERE `id` = ' . $this->id;
		$res = $this->db->q($q);
		return $res;
	}
}