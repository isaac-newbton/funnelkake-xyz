<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MicrositeRepository")
 */
class Microsite
{
    use EntityIdTrait;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Organization", inversedBy="microsite", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $organization;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domainName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $privacyPolicy;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $termsOfService;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaFile")
     */
    private $logo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaFile")
     */
    private $favicon;

    /**
     * @ORM\Column(type="json")
     */
    private $mailchimpSettings = [];

    /**
     * @ORM\Column(type="json")
     */
    private $socialLinks = [];

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $contactPhoneNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->enabled = true;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getDomainName(): ?string
    {
        return $this->domainName;
    }

    public function setDomainName(string $domainName): self
    {
        $this->domainName = $domainName;

        return $this;
    }

    public function getPrivacyPolicy(): ?string
    {
        return $this->privacyPolicy;
    }

    public function setPrivacyPolicy(?string $privacyPolicy): self
    {
        $this->privacyPolicy = $privacyPolicy;

        return $this;
    }

    public function getTermsOfService(): ?string
    {
        return $this->termsOfService;
    }

    public function setTermsOfService(?string $termsOfService): self
    {
        $this->termsOfService = $termsOfService;

        return $this;
    }

    public function getLogo(): ?MediaFile
    {
        return $this->logo;
    }

    public function setLogo(?MediaFile $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getFavicon(): ?MediaFile
    {
        return $this->favicon;
    }

    public function setFavicon(?MediaFile $favicon): self
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function getMailchimpSettings(): ?array
    {
        return $this->mailchimpSettings;
    }

    public function setMailchimpSettings(array $mailchimpSettings): self
    {
        $this->mailchimpSettings = $mailchimpSettings;

        return $this;
    }

    public function getSocialLinks(): ?array
    {
        return $this->socialLinks;
    }

    public function setSocialLinks(array $socialLinks): self
    {
        $this->socialLinks = $socialLinks;

        return $this;
    }

    public function getContactPhoneNumber(): ?string
    {
        return $this->contactPhoneNumber;
    }

    public function setContactPhoneNumber(?string $contactPhoneNumber): self
    {
        $this->contactPhoneNumber = $contactPhoneNumber;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
