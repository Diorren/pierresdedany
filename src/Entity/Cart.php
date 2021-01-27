<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CartRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Cart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="carts")
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $amount_ht = 0;

    /**
     * @ORM\Column(type="float")
     */
    private $amount_ttc = 0;

    /**
     * @ORM\Column(type="float")
     */
    private $shipping_ht = 0;

    /**
     * @ORM\Column(type="float")
     */
    private $shipping_ttc = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity=Cartline::class, mappedBy="cart")
     */
    private $cartlines;

    /**
     * @ORM\Column(type="boolean")
     */
    private $statut = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payment_id;

    public function __construct()
    {
        $this->cartlines = new ArrayCollection();
        $this->created_at = new \DateTime();
        $this->ref = strtoupper($this->random_id());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAmountHt(): ?float
    {
        return $this->amount_ht;
    }

    public function setAmountHt(float $amount_ht): self
    {
        $this->amount_ht = $amount_ht;

        return $this;
    }

    public function getAmountTtc(): ?float
    {
        return $this->amount_ttc;
    }

    public function setAmountTtc(float $amount_ttc): self
    {
        $this->amount_ttc = $amount_ttc;

        return $this;
    }

    public function getShippingHt(): ?float
    {
        return $this->shipping_ht;
    }

    public function setShippingHt(float $shipping_ht): self
    {
        $this->shipping_ht = $shipping_ht;

        return $this;
    }

    public function getShippingTtc(): ?float
    {
        return $this->shipping_ttc;
    }

    public function setShippingTtc(float $shipping_ttc): self
    {
        $this->shipping_ttc = $shipping_ttc;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection|Cartline[]
     */
    public function getCartlines(): Collection
    {
        return $this->cartlines;
    }

    public function addCartline(Cartline $cartline): self
    {
        if (!$this->cartlines->contains($cartline)) {
            $this->cartlines[] = $cartline;
            $cartline->setCart($this);
        }

        return $this;
    }

    public function removeCartline(Cartline $cartline): self
    {
        if ($this->cartlines->contains($cartline)) {
            $this->cartlines->removeElement($cartline);
            // set the owning side to null (unless already changed) 
            if ($cartline->getCart() === $this) {
                $cartline->setCart(null);
            }
        }

        return $this;
    }

    public function getStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }
    /**
     * Fonction qui permet de générer un uniqid de 8 caractères
     */
    private function random_id() {
        $rand = substr(uniqid(), -9, -1);
        return strtoupper($rand);
    }

    public function getPaymentId(): ?string
    {
        return $this->payment_id;
    }

    public function setPaymentId(?string $payment_id): self
    {
        $this->payment_id = $payment_id;

        return $this;
    }
}