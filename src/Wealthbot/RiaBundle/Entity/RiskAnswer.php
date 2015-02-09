<?php

namespace Wealthbot\RiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wealthbot\RiaBundle\Entity\RiskAnswer
 */
class RiskAnswer
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $risk_question_id
     */
    private $risk_question_id;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var \Wealthbot\RiaBundle\Entity\RiskQuestion
     */
    private $question;

    /**
     * @var integer $point
     */
    private $point = 0;

    /**
     * Set id
     *
     * @param integer $id
     * @return RiskQuestion
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set risk_question_id
     *
     * @param integer $riskQuestionId
     * @return RiskAnswer
     */
    public function setRiskQuestionId($riskQuestionId)
    {
        $this->risk_question_id = $riskQuestionId;
    
        return $this;
    }

    /**
     * Get risk_question_id
     *
     * @return integer 
     */
    public function getRiskQuestionId()
    {
        return $this->risk_question_id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return RiskAnswer
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set question
     *
     * @param \Wealthbot\RiaBundle\Entity\RiskQuestion $question
     * @return RiskAnswer
     */
    public function setQuestion(\Wealthbot\RiaBundle\Entity\RiskQuestion $question = null)
    {
        $this->question = $question;
    
        return $this;
    }

    /**
     * Get question
     *
     * @return \Wealthbot\RiaBundle\Entity\RiskQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set point
     *
     * @param integer $point
     * @return RiskAnswer
     */
    public function setPoint($point)
    {
        $this->point = $point;
    
        return $this;
    }

    /**
     * Get point
     *
     * @return integer 
     */
    public function getPoint()
    {
        return $this->point;
    }
}
