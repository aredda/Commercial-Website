<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PurchaseRepository")
 */
class Purchase
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="datetime")
     */
    public $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="purchases", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    public $customer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PurchaseDetail", mappedBy="purchase", orphanRemoval=true, fetch="EAGER")
     */
    private $purchaseDetails;

    public function __construct()
    {
        $this->purchaseDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection|PurchaseDetail[]
     */
    public function getPurchaseDetails(): Collection
    {
        return $this->purchaseDetails;
    }

    public function addPurchaseDetail(PurchaseDetail $purchaseDetail): self
    {
        if (!$this->purchaseDetails->contains($purchaseDetail)) {
            $this->purchaseDetails[] = $purchaseDetail;
            $purchaseDetail->setPurchase($this);
        }

        return $this;
    }

    public function removePurchaseDetail(PurchaseDetail $purchaseDetail): self
    {
        if ($this->purchaseDetails->contains($purchaseDetail)) {
            $this->purchaseDetails->removeElement($purchaseDetail);
            // set the owning side to null (unless already changed)
            if ($purchaseDetail->getPurchase() === $this) {
                $purchaseDetail->setPurchase(null);
            }
        }

        return $this;
    }
}
