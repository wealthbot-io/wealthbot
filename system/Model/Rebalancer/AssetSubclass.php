<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class AssetSubclass extends Base {

	private $name;

	private $deleted = false;

	private $topOob;

    private $targetPercent;

    private $currentPercent;

    private $toleranceBand;

	public function name($val = null) {
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

	public function deleted($val = null) {
        if (null !== $val) {
			$this->deleted = $val;
		}

		return $this->deleted;
	}

	public function topOob($val = null) {
		if (null !== $val) {
			$this->topOob = $val;
		}

		return $this->topOob;
	}

    public function targetPercent($val = null)
    {
        if (null !== $val) {
            $this->targetPercent = $val;
        }

        return $this->targetPercent;
    }

    public function currentPercent($val = null)
    {
        if (null !== $val) {
            $this->currentPercent = $val;
        }

        return $this->currentPercent;
    }

    public function calcOob()
    {
        return $this->currentPercent() - $this->targetPercent();
    }

    private function __getData()
    {
        $q = 'SELECT * FROM subclasses s WHERE s.id = '.$this->id;
        $res = $this->db->q($q);
        return $res;
    }
}