<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.07.13
 * Time: 15:31
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="tempQuestionnaire")
 */
class TempQuestionnaire
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\int
     */
    protected $clientUserId;

    /**
     * @MongoDB\int
     */
    protected $questionId;

    /**
     * @MongoDB\int
     */
    protected $answerId;

    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set clientUserId.
     *
     * @param string $clientUserId
     *
     * @return self
     */
    public function setClientUserId($clientUserId)
    {
        $this->clientUserId = $clientUserId;

        return $this;
    }

    /**
     * Get clientUserId.
     *
     * @return string $clientUserId
     */
    public function getClientUserId()
    {
        return $this->clientUserId;
    }

    /**
     * Set questionId.
     *
     * @param int $questionId
     *
     * @return self
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;

        return $this;
    }

    /**
     * Get questionId.
     *
     * @return int $questionId
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * Set answerId.
     *
     * @param int $answerId
     *
     * @return self
     */
    public function setAnswerId($answerId)
    {
        $this->answerId = $answerId;

        return $this;
    }

    /**
     * Get answerId.
     *
     * @return int $answerId
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }
}
