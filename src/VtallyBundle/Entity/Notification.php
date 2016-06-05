<?php

namespace VtallyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="VtallyBundle\Repository\NotificationRepository")
 */
class Notification
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
     * @var string
     *
     * @ORM\Column(name="firstVerifier", type="string", length=255)
     */
    private $firstVerifier;

    /**
     * @var string
     *
     * @ORM\Column(name="secondVerifier", type="string", length=255)
     */
    private $secondVerifier;

    /**
     * @var string
     *
     * @ORM\Column(name="pollingStation", type="string", length=255)
     */
    private $pollingStation;


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
     * Set firstVerifier
     *
     * @param string $firstVerifier
     *
     * @return Notification
     */
    public function setFirstVerifier($firstVerifier)
    {
        $this->firstVerifier = $firstVerifier;

        return $this;
    }

    /**
     * Get firstVerifier
     *
     * @return string
     */
    public function getFirstVerifier()
    {
        return $this->firstVerifier;
    }

    /**
     * Set secondVerifier
     *
     * @param string $secondVerifier
     *
     * @return Notification
     */
    public function setSecondVerifier($secondVerifier)
    {
        $this->secondVerifier = $secondVerifier;

        return $this;
    }

    /**
     * Get secondVerifier
     *
     * @return string
     */
    public function getSecondVerifier()
    {
        return $this->secondVerifier;
    }

    /**
     * Set pollingStation
     *
     * @param string $pollingStation
     *
     * @return Notification
     */
    public function setPollingStation($pollingStation)
    {
        $this->pollingStation = $pollingStation;

        return $this;
    }

    /**
     * Get pollingStation
     *
     * @return string
     */
    public function getPollingStation()
    {
        return $this->pollingStation;
    }
}
