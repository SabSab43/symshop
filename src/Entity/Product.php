<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @Vich\Uploadable
 * @HasLifecycleCallbacks
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
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $mainPicture;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * 
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="mainPicture")
     * @Assert\Image(
     *      mimeTypes = {"image/jpeg", "image/jpg"},
     *      mimeTypesMessage = "Le format de l'image est incorrect."
     * )
     * 
     * @var File|null
     */
    private $mainPictureFile;

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

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->purchaseItems = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if ($this->updatedAt === null) {
            $this->updatedAt = new DateTime('now');
        }
        if ($this->isDisplayed === null) {
            $this->isDisplayed = true;
        }
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
   
    public function getMainPictureFile(): ?File
    {
        return $this->mainPictureFile;
    }

    public function setMainPictureFile(?File $mainPictureFile = null): void
    {
        $this->mainPictureFile = $mainPictureFile;

        // Only change the updated af if the file is really uploaded to avoid database updates.
        // This is needed when the file should be set when loading the entity.
        if ($this->mainPictureFile instanceof UploadedFile) {
            $this->updatedAt = new \DateTime('now');
        }
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
