<?php

namespace App\Entity;

use App\Repository\PictureGalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PictureGalleryRepository::class)]
class PictureGallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $pictureName = null;

    #[ORM\Column(length: 255)]
    private ?string $filePath = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class, mappedBy: 'galleries')]
    private Collection $activities;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPictureName(): ?string
    {
        return $this->pictureName;
    }

    public function setPictureName(string $pictureName): static
    {
        $this->pictureName = $pictureName;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self // static
    {
        if (!$this->activities->contains($activity)) {
            // $this->activities->add($activity);
            $this->activities[] = $activity;
            $activity->addGallery($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self // static
    {
        if ($this->activities->removeElement($activity)) {
            $activity->removeGallery($this);
        }

        return $this;
    }
}
