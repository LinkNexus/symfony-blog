<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, CommentReaction>
     */
    #[ORM\OneToMany(targetEntity: CommentReaction::class, mappedBy: 'comment')]
    private Collection $commentReactions;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->commentReactions = new ArrayCollection();
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array(
            "id" => $this->id,
            "content" => $this->content,
            "createdAt" => $this->createdAt,
            "updatedAt" => $this->updatedAt,
            "post" => $this->post,
            "author" => $this->owner,
            "reactions" => $this->commentReactions->toArray()
        );
    }

    /**
     * @return Collection<int, CommentReaction>
     */
    public function getCommentReactions(): Collection
    {
        return $this->commentReactions;
    }

    public function addCommentReaction(CommentReaction $commentReaction): static
    {
        if (!$this->commentReactions->contains($commentReaction)) {
            $this->commentReactions->add($commentReaction);
            $commentReaction->setComment($this);
        }

        return $this;
    }

    public function removeCommentReaction(CommentReaction $commentReaction): static
    {
        if ($this->commentReactions->removeElement($commentReaction)) {
            // set the owning side to null (unless already changed)
            if ($commentReaction->getComment() === $this) {
                $commentReaction->setComment(null);
            }
        }

        return $this;
    }
}
