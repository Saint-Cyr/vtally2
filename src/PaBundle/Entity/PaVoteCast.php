<?php

namespace PaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaVoteCast
 *
 * @ORM\Table(name="pa_vote_cast")
 * @ORM\Entity(repositoryClass="PaBundle\Repository\PaVoteCastRepository")
 */
class PaVoteCast
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
     * @ORM\ManyToOne(targetEntity="VtallyBundle\Entity\PollingStation", inversedBy="paVoteCasts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pollingStation;
    
    /**
     * @ORM\ManyToOne(targetEntity="PaBundle\Entity\DependentCandidate", inversedBy="paVoteCasts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $dependentCandidate;
    
    /**
     * @ORM\ManyToOne(targetEntity="PaBundle\Entity\IndependentCandidate", inversedBy="paVoteCasts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $independentCandidate;


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
     * @return PaVoteCast
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
     * @return PaVoteCast
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
     * @return PaVoteCast
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
     * @return PaVoteCast
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
     * Set dependentCandidate
     *
     * @param \PaBundle\Entity\DependentCandidate $dependentCandidate
     *
     * @return PaVoteCast
     */
    public function setDependentCandidate(\PaBundle\Entity\DependentCandidate $dependentCandidate = null)
    {
        $this->dependentCandidate = $dependentCandidate;

        return $this;
    }

    /**
     * Get dependentCandidate
     *
     * @return \PaBundle\Entity\DependentCandidate
     */
    public function getDependentCandidate()
    {
        return $this->dependentCandidate;
    }

    /**
     * Set independentCandidate
     *
     * @param \PaBundle\Entity\IndependentCandidate $independentCandidate
     *
     * @return PaVoteCast
     */
    public function setIndependentCandidate(\PaBundle\Entity\IndependentCandidate $independentCandidate = null)
    {
        $this->independentCandidate = $independentCandidate;

        return $this;
    }

    /**
     * Get independentCandidate
     *
     * @return \PaBundle\Entity\IndependentCandidate
     */
    public function getIndependentCandidate()
    {
        return $this->independentCandidate;
    }
}
