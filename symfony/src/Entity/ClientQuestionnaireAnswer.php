<?php

namespace App\Entity;

/**
 * Class ClientQuestionnaireAnswer
 * @package App\Entity
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
     * @var \App\Entity\User
     */
    private $client;

    /**
     * @var \App\Entity\RiskQuestion
     */
    private $question;

    /**
     * @var \App\Entity\RiskAnswer
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
     * @param \App\Entity\User $client
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set question.
     *
     * @param \App\Entity\RiskQuestion $question
     *
     * @return ClientQuestionnaireAnswer
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
     * Set answer.
     *
     * @param \App\Entity\RiskAnswer $answer
     *
     * @return ClientQuestionnaireAnswer
     */
    public function setAnswer(RiskAnswer $answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return \App\Entity\RiskAnswer
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
