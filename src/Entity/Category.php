<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="category", orphanRemoval=true)
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $itsProduct): self
    {
        if (!$this->products->contains($itsProduct)) {
            $this->products[] = $itsProduct;
            $itsProduct->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $itsProduct): self
    {
        if ($this->products->contains($itsProduct)) {
            $this->products->removeElement($itsProduct);
            // set the owning side to null (unless already changed)
            if ($itsProduct->getCategory() === $this) {
                $itsProduct->setCategory(null);
            }
        }

        return $this;
    }
}
