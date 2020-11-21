<?php

namespace App\Entity;

/**
 * Class TransactionType
 * @package App\Entity
 */
class TransactionType
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $activity;

    /**
     * @var string
     */
    private $report_as;

    /**
     * @var string
     */
    private $description;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return TransactionType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return TransactionType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set activity.
     *
     * @param string $activity
     *
     * @return TransactionType
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity.
     *
     * @return string
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set report_as.
     *
     * @param string $reportAs
     *
     * @return TransactionType
     */
    public function setReportAs($reportAs)
    {
        $this->report_as = $reportAs;

        return $this;
    }

    /**
     * Get report_as.
     *
     * @return string
     */
    public function getReportAs()
    {
        return $this->report_as;
    }
}
