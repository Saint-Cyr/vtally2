<?php

namespace PrBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrVoteCast
 *
 * @ORM\Table(name="pr_vote_cast")
 * @ORM\Entity(repositoryClass="PrBundle\Repository\PrVoteCastRepository")
 */
class PrVoteCast
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="figureValue", type="integer")
     */
    private $figureValue;

    /**
     * @var string
     *
     * @ORM\Column(name="wordValue", type="string", length=255, nullable=true)
     */
    private $wordValue;

    /**
     * @var bool
     *
     * @ORM\Column(name="edited", type="boolean", nullable=true)
     */
    private $edited;

    /**
     * @ORM\ManyToOne(targetEntity="VtallyBundle\Entity\PollingStation", inversedBy="prVoteCasts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pollingStation;
    
    /**
     * @ORM\ManyToOne(targetEntity="PrBundle\Entity\PrParty", inversedBy="prVoteCasts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prParty;
    
    public function __toString() {
        return $this->wordValue;
    }
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set figureValue
     *
     * @param integer $figureValue
     *
     * @return PrVoteCast
     */
    public function setFigureValue($figureValue)
    {
        $this->figureValue = $figureValue;

        return $this;
    }

    /**
     * Get figureValue
     *
     * @return int
     */
    public function getFigureValue()
    {
        return $this->figureValue;
    }

    /**
     * Set wordValue
     *
     * @param string $wordValue
     *
     * @return PrVoteCast
     */
    public function setWordValue($wordValue)
    {
        $this->wordValue = $wordValue;

        return $this;
    }

    /**
     * Get wordValue
     *
     * @return string
     */
    public function getWordValue()
    {
        return $this->wordValue;
    }

    /**
     * Set edited
     *
     * @param boolean $edited
     *
     * @return PrVoteCast
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * Get edited
     *
     * @return bool
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * Set pollingStation
     *
     * @param \VtallyBundle\Entity\PollingStation $pollingStation
     *
     * @return PrVoteCast
     */
    public function setPollingStation(\VtallyBundle\Entity\PollingStation $pollingStation)
    {
        $this->pollingStation = $pollingStation;

        return $this;
    }

    /**
     * Get pollingStation
     *
     * @return \VtallyBundle\Entity\PollingStation
     */
    public function getPollingStation()
    {
        return $this->pollingStation;
    }

    /**
     * Set prParty
     *
     * @param \PrBundle\Entity\PrParty $prParty
     *
     * @return PrVoteCast
     */
    public function setPrParty(\PrBundle\Entity\PrParty $prParty)
    {
        $this->prParty = $prParty;

        return $this;
    }

    /**
     * Get prParty
     *
     * @return \PrBundle\Entity\PrParty
     */
    public function getPrParty()
    {
        return $this->prParty;
    }
}
