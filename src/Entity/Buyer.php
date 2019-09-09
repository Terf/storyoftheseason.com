<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BuyerRepository")
 */
class Buyer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $lastName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="buyers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seller", inversedBy="buyers")
     */
    private $seller;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase", mappedBy="user", orphanRemoval=true)
     */
    private $purchases;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Upload", mappedBy="user")
     */
    private $uploads;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $token;

    public const TYPES = [
        1 => 'Parent',
        2 => 'Player',
        3 => 'Alumni',
        4 => 'Student',
        5 => 'Family friend',
        6 => 'Community member',
        7 => 'Other'
    ];

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
        $this->uploads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection|Purchase[]
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): self
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases[] = $purchase;
            $purchase->setUser($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): self
    {
        if ($this->purchases->contains($purchase)) {
            $this->purchases->removeElement($purchase);
            // set the owning side to null (unless already changed)
            if ($purchase->getUser() === $this) {
                $purchase->setUser(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getType(): ?string
    {
        return (array_key_exists($this->type, self::TYPES)) ? self::TYPES[$this->type] : $this->type;
    }

    // todo: change this method to int $type
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Upload[]
     */
    public function getUploads(): Collection
    {
        return $this->uploads;
    }

    public function addUpload(Upload $upload): self
    {
        if (!$this->uploads->contains($upload)) {
            $this->uploads[] = $upload;
            $upload->setUser($this);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): self
    {
        if ($this->uploads->contains($upload)) {
            $this->uploads->removeElement($upload);
            // set the owning side to null (unless already changed)
            if ($upload->getUser() === $this) {
                $upload->setUser(null);
            }
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
