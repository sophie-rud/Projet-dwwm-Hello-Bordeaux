<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nameCategory = null;

    #[ORM\Column(length: 255)]
    private ?string $pictogram = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'category')]
    private Collection $activity;

    public function __construct()
    {
        $this->activity = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameCategory(): ?string
    {
        return $this->nameCategory;
    }

    public function setNameCategory(string $nameCategory): static
    {
        $this->nameCategory = $nameCategory;

        return $this;
    }

    public function getPictogram(): ?string
    {
        return $this->pictogram;
    }

    public function setPictogram(string $pictogram): static
    {
        $this->pictogram = $pictogram;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivity(): Collection
    {
        return $this->activity;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activity->contains($activity)) {
            $this->activity->add($activity);
            $activity->setCategory($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activity->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getCategory() === $this) {
                $activity->setCategory(null);
            }
        }

        return $this;
    }
}
