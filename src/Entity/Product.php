<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase", mappedBy="product", orphanRemoval=true)
     */
    private $purchases;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Book", mappedBy="product", orphanRemoval=true)
     */
    private $books;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Coupon", mappedBy="product", orphanRemoval=true)
     */
    private $coupons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProspectiveBuyer", mappedBy="product", orphanRemoval=true)
     */
    private $prospectiveBuyers;

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
        $this->books = new ArrayCollection();
        $this->coupons = new ArrayCollection();
        $this->prospectiveBuyers = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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
            $purchase->setProduct($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): self
    {
        if ($this->purchases->contains($purchase)) {
            $this->purchases->removeElement($purchase);
            // set the owning side to null (unless already changed)
            if ($purchase->getProduct() === $this) {
                $purchase->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Book[]
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            $book->setProduct($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->contains($book)) {
            $this->books->removeElement($book);
            // set the owning side to null (unless already changed)
            if ($book->getProduct() === $this) {
                $book->setProduct(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Coupon[]
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    public function addCoupon(Coupon $coupon): self
    {
        if (!$this->coupons->contains($coupon)) {
            $this->coupons[] = $coupon;
            $coupon->setProduct($this);
        }

        return $this;
    }

    public function removeCoupon(Coupon $coupon): self
    {
        if ($this->coupons->contains($coupon)) {
            $this->coupons->removeElement($coupon);
            // set the owning side to null (unless already changed)
            if ($coupon->getProduct() === $this) {
                $coupon->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProspectiveBuyer[]
     */
    public function getProspectiveBuyers(): Collection
    {
        return $this->prospectiveBuyers;
    }

    public function addProspectiveBuyer(ProspectiveBuyer $prospectiveBuyer): self
    {
        if (!$this->prospectiveBuyers->contains($prospectiveBuyer)) {
            $this->prospectiveBuyers[] = $prospectiveBuyer;
            $prospectiveBuyer->setProduct($this);
        }

        return $this;
    }

    public function removeProspectiveBuyer(ProspectiveBuyer $prospectiveBuyer): self
    {
        if ($this->prospectiveBuyers->contains($prospectiveBuyer)) {
            $this->prospectiveBuyers->removeElement($prospectiveBuyer);
            // set the owning side to null (unless already changed)
            if ($prospectiveBuyer->getProduct() === $this) {
                $prospectiveBuyer->setProduct(null);
            }
        }

        return $this;
    }
}
