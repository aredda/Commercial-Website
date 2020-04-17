<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PurchaseDetailRepository")
 */
class PurchaseDetail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    public $product;

    /**
     * @ORM\Column(type="integer")
     */
    public $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Purchase", inversedBy="purchaseDetails")
     * @ORM\JoinColumn(nullable=false)
     */
    public $purchase;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPurchase(): ?Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(?Purchase $purchase): self
    {
        $this->purchase = $purchase;

        return $this;
    }

    public function getDetailTotalPrice ()
    {
        return $this->getProduct()->getPrice () * $this->quantity;
    }
}
