<?php

namespace App\Entity;

/**
 * Class RiskAnswer
 * @package App\Entity
 */
class RiskAnswer
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $risk_question_id;

    /**
     * @var string
     */
    private $title;

    /**
     * @param \App\Entity\RiskQuestion
     */
    private $question;

    /**
     * @var int
     */
    private $point = 0;

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return RiskQuestion
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set risk_question_id.
     *
     * @param int $riskQuestionId
     *
     * @return RiskAnswer
     */
    public function setRiskQuestionId($riskQuestionId)
    {
        $this->risk_question_id = $riskQuestionId;

        return $this;
    }

    /**
     * Get risk_question_id.
     *
     * @return int
     */
    public function getRiskQuestionId()
    {
        return $this->risk_question_id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return RiskAnswer
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set question.
     *
     * @param \App\Entity\RiskQuestion $question
     *
     * @return RiskAnswer
     */
    public function setQuestion(RiskQuestion $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question.
     *
     * @return \App\Entity\RiskQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set point.
     *
     * @param int $point
     *
     * @return RiskAnswer
     */
    public function setPoint($point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point.
     *
     * @return int
     */
    public function getPoint()
    {
        return $this->point;
    }

    public function __toString()
    {
        return (string) $this->title;
    }
}
