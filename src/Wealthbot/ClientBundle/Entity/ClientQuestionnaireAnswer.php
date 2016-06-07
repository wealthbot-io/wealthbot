<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * Wealthbot\ClientBundle\Entity\ClientQuestionnaireAnswer.
 */
class ClientQuestionnaireAnswer
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_id;

    /**
     * @var int
     */
    private $question_id;

    /**
     * @var int
     */
    private $answer_id;

    /**
     * @var Wealthbot\UserBundle\Entity\User
     */
    private $client;

    /**
     * @var Wealthbot\RiaBundle\Entity\RiskQuestion
     */
    private $question;

    /**
     * @var Wealthbot\RiaBundle\Entity\RiskAnswer
     */
    private $answer;

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
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set question_id.
     *
     * @param int $questionId
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setQuestionId($questionId)
    {
        $this->question_id = $questionId;

        return $this;
    }

    /**
     * Get question_id.
     *
     * @return int
     */
    public function getQuestionId()
    {
        return $this->question_id;
    }

    /**
     * Set answer_id.
     *
     * @param int $answerId
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setAnswerId($answerId)
    {
        $this->answer_id = $answerId;

        return $this;
    }

    /**
     * Get answer_id.
     *
     * @return int
     */
    public function getAnswerId()
    {
        return $this->answer_id;
    }

    /**
     * Set client.
     *
     * @param Wealthbot\UserBundle\Entity\User $client
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setClient(\Wealthbot\UserBundle\Entity\User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return Wealthbot\UserBundle\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set question.
     *
     * @param Wealthbot\RiaBundle\Entity\RiskQuestion $question
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setQuestion(\Wealthbot\RiaBundle\Entity\RiskQuestion $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question.
     *
     * @return Wealthbot\RiaBundle\Entity\RiskQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set answer.
     *
     * @param Wealthbot\RiaBundle\Entity\RiskAnswer $answer
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setAnswer(\Wealthbot\RiaBundle\Entity\RiskAnswer $answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return Wealthbot\RiaBundle\Entity\RiskAnswer
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
