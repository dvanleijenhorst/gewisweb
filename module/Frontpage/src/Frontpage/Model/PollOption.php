<?php

namespace Frontpage\Model;

use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Poll Option
 *
 * @ORM\Entity
 * @ORM\Table(name="PollOption")
 */
class PollOption implements ResourceInterface
{

    /**
     * Poll Option ID.
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Frontpage\Model\Poll", inversedBy="options")
     * @ORM\JoinColumn(name="poll_id",referencedColumnName="id")
     */
    protected $poll;

    /**
     * The dutch text for this option.
     *
     * @ORM\Column(type="string")
     */
    protected $dutchText;

    /**
     * The english translation of the option if available.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $englishText;

    /**
     * @ORM\OneToMany(targetEntity="PollResponse", mappedBy="pollOption", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    protected $responses;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDutchText()
    {
        return $this->dutchText;
    }

    /**
     * @return string
     */
    public function getEnglishText()
    {
        return $this->englishText;
    }

    /**
     * Get the number of votes for this poll option.
     *
     * @return integer
     */
    public function getResponseCount()
    {
        return $this->responses->count();
    }

    /**
     * Get the resource ID.
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'poll_option';
    }
}