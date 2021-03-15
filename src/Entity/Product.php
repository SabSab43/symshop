<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du produit est obligatoire.")
     * @Assert\Length(
     *  min=3,
     *  max=255, 
     *  minMessage="La longueur du nom doit être d'au moins {{ limit }} caractères.", 
     *  maxMessage="La longueur du nom doit être au maximum de {{ limit }} caractères."
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Le prix est obligatoire.")
     * @Assert\GreaterThan(0, message="Le prix minimal du produit doit être positif.")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @Assert\NotBlank(message="La catégorie du produit est obligatoire.")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mainPicture;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="La description courte est obligatoire.")
     * @Assert\Length(
     *  min=20,
     *  max=255, 
     *  minMessage="La longueur de la description courte doit être d'au moins {{ limit }} caractères.", 
     *  maxMessage="La longueur de la description courte doit être au maximum de {{ limit }} caractères."
     * )
     */
    private $shortDescription;

    /**
     * @ORM\OneToMany(targetEntity=PurchaseItem::class, mappedBy="product")
     */
    private $purchaseItems;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isForward;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDisplayed;

    public function __construct()
    {
        $this->purchaseItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getMainPicture(): ?string
    {
        return $this->mainPicture;
    }

    public function setMainPicture(?string $mainPicture): self
    {
        $this->mainPicture = $mainPicture;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    
    public function getIsForward(): ?bool
    {
        return $this->isForward;
    }

    public function setIsForward(bool $isForward): self
    {
        $this->isForward = $isForward;

        return $this;
    }

    public function getIsDisplayed(): ?bool
    {
        return $this->isDisplayed;
    }

    public function setIsDisplayed(bool $isDisplayed): self
    {
        $this->isDisplayed = $isDisplayed;

        return $this;
    }

    /**
     * @return Collection|PurchaseItem[]
     */
    public function getPurchaseItems(): Collection
    {
        return $this->purchaseItems;
    }

    public function addPurchaseItem(PurchaseItem $purchaseItem): self
    {
        if (!$this->purchaseItems->contains($purchaseItem)) {
            $this->purchaseItems[] = $purchaseItem;
            $purchaseItem->setProduct($this);
        }

        return $this;
    }

    public function removePurchaseItem(PurchaseItem $purchaseItem): self
    {
        if ($this->purchaseItems->removeElement($purchaseItem)) {
            // set the owning side to null (unless already changed)
            if ($purchaseItem->getProduct() === $this) {
                $purchaseItem->setProduct(null);
            }
        }

        return $this;
    }
    
    /**
     * if isForward === true => return all forwards products
     * if isForward === false => return all not forwards products
     *
     * @param  array $products
     * @param  bool $isForward
     * @return array $sortedProducts
     */
    public static function sortForwards(array $products, bool $isForward)
    {
        foreach ($products as $p) {
            /** @var Product */
            if ($p->isForward === $isForward) {
                $sortedProducts[] = $p;
            }
        }
        return $sortedProducts;
    }
}
