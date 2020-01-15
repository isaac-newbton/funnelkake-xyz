<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TicketRepository")
 */
class Ticket
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="tickets")
     */
    private $organization;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="ticket")
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;
    
    const STATUS_CLOSED = 0;
    const STATUS_OPEN = 1;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\MediaFile", mappedBy="ticket")
     */
    private $mediaFiles;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="tickets")
     */
    private $users;

    public function __construct()
    {
        $this->comment = new ArrayCollection();
        $this->mediaFiles = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->status = self::STATUS_OPEN;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComment(): Collection
    {
        return $this->comment;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comment->contains($comment)) {
            $this->comment[] = $comment;
            $comment->setTicket($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comment->contains($comment)) {
            $this->comment->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTicket() === $this) {
                $comment->setTicket(null);
            }
        }

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|MediaFile[]
     */
    public function getMediaFiles(): Collection
    {
        return $this->mediaFiles;
    }

    public function addMediaFile(MediaFile $mediaFile): self
    {
        if (!$this->mediaFiles->contains($mediaFile)) {
            $this->mediaFiles[] = $mediaFile;
            $mediaFile->addTicket($this);
        }

        return $this;
    }

    public function removeMediaFile(MediaFile $mediaFile): self
    {
        if ($this->mediaFiles->contains($mediaFile)) {
            $this->mediaFiles->removeElement($mediaFile);
            $mediaFile->removeTicket($this);
        }

        return $this;
    }

    public function getStatusText(): string
    {
        switch ($this->status) {
            case self::STATUS_CLOSED :
                return "closed";
            break;

            case self::STATUS_OPEN :
                return "open";
            break;

            default :
                return "open";
            break;
        }
    }

    public function close(): self
    {
        $this->setStatus(self::STATUS_CLOSED);
        return $this;
    }

    public function open(): self
    {
        $this->setStatus(self::STATUS_OPEN);
        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }
}
