<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 24.06.13
 * Time: 15:36
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Entity\SecurityAssignment;
use App\Entity\Subclass;

interface CeModelEntityInterface
{
    /**
     * Set is qualified.
     *
     * @param $isQualified
     *
     * @return self
     */
    public function setIsQualified($isQualified);

    /**
     * Get is qualified.
     *
     * @return bool
     */
    public function getIsQualified();

    /**
     * Set percent.
     *
     * @param float $percent
     *
     * @return self
     */
    public function setPercent($percent);

    /**
     * Get percent.
     *
     * @return float
     */
    public function getPercent();

    /**
     * Set model.
     *
     * @param CeModelInterface $model
     *
     * @return self
     */
    public function setModel(CeModelInterface $model = null);

    /**
     * Get model.
     *
     * @return CeModelInterface
     */
    public function getModel();

    /**
     * Set securityAssignment.
     *
     * @param SecurityAssignment $securityAssignment
     *
     * @return self
     */
    public function setSecurityAssignment(SecurityAssignment $securityAssignment);

    /**
     * Get securityAssignment.
     *
     * @return SecurityAssignment
     */
    public function getSecurityAssignment();

    /**
     * Set id of subclass.
     *
     * @param int $subclassId
     *
     * @return self
     */
    public function setSubclassId($subclassId);

    /**
     * Get id of subclass.
     *
     * @return int
     */
    public function getSubclassId();

    /**
     * Set subclass.
     *
     * @param Subclass $subclass
     *
     * @return self
     */
    public function setSubclass(Subclass $subclass = null);

    /**
     * Get subclass.
     *
     * @return Subclass
     */
    public function getSubclass();

    /**
     * Return array of securityAssignment name and percent values.
     *
     * @return array
     */
    public function toArray();

    /**
     * Copy model entity
     * returns new model entity with exist model entity data.
     *
     * @return self
     */
    public function getCopy();
}
