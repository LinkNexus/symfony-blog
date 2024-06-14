<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[Broadcast]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Please write the content of your post before submitting')]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $restrictionType = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'posts')]
    private Collection $categories;

    #[ORM\OneToOne(mappedBy: 'post', cascade: ['persist', 'remove'])]
    private ?PostRestriction $postRestriction = null;

    /**
     * @var Collection<int, PostReaction>
     */
    #[ORM\OneToMany(targetEntity: PostReaction::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $postReactions;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->categories = new ArrayCollection();
        $this->postReactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRestrictionType(): ?string
    {
        return $this->restrictionType;
    }

    public function setRestrictionType(?string $restrictionType): static
    {
        $this->restrictionType = $restrictionType;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getPostRestriction(): ?PostRestriction
    {
        return $this->postRestriction;
    }

    public function setPostRestriction(?PostRestriction $postRestriction): static
    {
        // unset the owning side of the relation if necessary
        if ($postRestriction === null && $this->postRestriction !== null) {
            $this->postRestriction->setPost(null);
        }

        // set the owning side of the relation if necessary
        if ($postRestriction !== null && $postRestriction->getPost() !== $this) {
            $postRestriction->setPost($this);
        }

        $this->postRestriction = $postRestriction;

        return $this;
    }

    public function __toString(): string
    {
        return 'Post #'. $this->id;
    }

    /**
     * @return Collection<int, PostReaction>
     */
    public function getPostReactions(): Collection
    {
        return $this->postReactions;
    }

    public function addPostReaction(PostReaction $postReaction): static
    {
        if (!$this->postReactions->contains($postReaction)) {
            $this->postReactions->add($postReaction);
            $postReaction->setPost($this);
        }

        return $this;
    }

    public function removePostReaction(PostReaction $postReaction): static
    {
        if ($this->postReactions->removeElement($postReaction)) {
            // set the owning side to null (unless already changed)
            if ($postReaction->getPost() === $this) {
                $postReaction->setPost(null);
            }
        }

        return $this;
    }
}
