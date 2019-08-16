<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SellerRepository")
 */
class Seller
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Buyer", mappedBy="seller")
     */
    private $buyers;

    private const TYPES = [
        1 => 'Parent',
        2 => 'Player',
        3 => 'Alumni',
        4 => 'Student'
    ];

    public function __construct()
    {
        $this->buyers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return (in_array($this->type, self::TYPES)) ? self::TYPES[$this->type] : $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Buyer[]
     */
    public function getBuyers(): Collection
    {
        return $this->buyers;
    }

    public function addBuyer(Buyer $buyer): self
    {
        if (!$this->buyers->contains($buyer)) {
            $this->buyers[] = $buyer;
            $buyer->setSeller($this);
        }

        return $this;
    }

    public function removeBuyer(Buyer $buyer): self
    {
        if ($this->buyers->contains($buyer)) {
            $this->buyers->removeElement($buyer);
            // set the owning side to null (unless already changed)
            if ($buyer->getSeller() === $this) {
                $buyer->setSeller(null);
            }
        }

        return $this;
    }
}
