<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $place = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbParticipantsMax = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    private ?bool $isPublished = null;


    #[ORM\ManyToOne(inversedBy: 'catActivities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;


    /**
     * @var Collection<int, PictureGallery>
     */
    #[ORM\ManyToMany(targetEntity: PictureGallery::class, inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $gallery;

    #[ORM\ManyToOne(inversedBy: 'activitiesOrganized')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userAdminOrganizer = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'activitiesParticipate')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $userParticipant;

    public function __construct()
    {
        $this->gallery = new ArrayCollection();
        $this->userParticipant = new ArrayCollection();
        $this->createdAt = new \DateTime('NOW');
        $this->updatedAt = new \DateTime('NOW');

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNbParticipantsMax(): ?int
    {
        return $this->nbParticipantsMax;
    }

    public function setNbParticipantsMax(?int $nbParticipantsMax): static
    {
        $this->nbParticipantsMax = $nbParticipantsMax;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;

    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }



    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }



    /**
     * @return Collection<int, PictureGallery>
     */
    public function getGallery(): Collection
    {
        return $this->gallery;
    }

    public function addGallery(PictureGallery $gallery): static
    {
        if (!$this->gallery->contains($gallery)) {
            $this->gallery->add($gallery);
        }

        return $this;
    }

    public function removeGallery(PictureGallery $gallery): static
    {
        $this->gallery->removeElement($gallery);

        return $this;
    }

    public function getUserAdminOrganizer(): ?User
    {
        return $this->userAdminOrganizer;
    }

    public function setUserAdminOrganizer(?User $userAdminOrganizer): static
    {
        $this->userAdminOrganizer = $userAdminOrganizer;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserParticipant(): Collection
    {
        return $this->userParticipant;
    }

    public function addUserParticipant(User $userParticipant): static
    {
        if (!$this->userParticipant->contains($userParticipant)) {
            $this->userParticipant->add($userParticipant);
        }

        return $this;
    }

    public function removeUserParticipant(User $userParticipant): static
    {
        $this->userParticipant->removeElement($userParticipant);

        return $this;
    }


}
