<?php

namespace App\Entity;

use App\Repository\CalculationRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotBlankValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Expression;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CalculationRepository::class)
 */
class Calculation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CountryRate::class)
     * * @Assert\Expression(
     *     "this.isIsCustom() or this.getCountryRate() != null"
     * )
     */
    private $country_rate;

    /**
     * @ORM\Column(type="float")
     * 
     */
    private $net_amount;

    /**
     * @ORM\Column(type="float")
     */
    private $gross_amount;

    /**
     * @ORM\Column(type="boolean")
     */
    private $vat_added;

    /**
     * @ORM\Column(type="boolean")
     */
    private $vat_removed;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(type="float")
     * * @Assert\Expression(
     *     "!this.isIsCustom() or (this.getVatRate() != null and this.getVatRate() != '')"
     * )
     */
    private $vat_rate;

    /**
     * @ORM\Column(type="float")
     */
    private $vat_amount;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_custom;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank
     * @Assert\Type("float")
     */
    private $based_on;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountryRate(): ?CountryRate
    {
        return $this->country_rate;
    }

    public function setCountryRate(?CountryRate $country_rate): self
    {
        $this->country_rate = $country_rate;

        return $this;
    }

    public function getNetAmount(): ?float
    {
        return $this->net_amount;
    }

    public function setNetAmount(float $net_amount): self
    {
        $this->net_amount = $net_amount;

        return $this;
    }

    public function getGrossAmount(): ?float
    {
        return $this->gross_amount;
    }

    public function setGrossAmount(float $gross_amount): self
    {
        $this->gross_amount = $gross_amount;

        return $this;
    }

    public function isVatAdded(): ?bool
    {
        return $this->vat_added;
    }

    public function setVatAdded(bool $vat_added): self
    {
        $this->vat_added = $vat_added;

        return $this;
    }

    public function isVatRemoved(): ?bool
    {
        return $this->vat_removed;
    }

    public function setVatRemoved(bool $vat_removed): self
    {
        $this->vat_removed = $vat_removed;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getVatRate(): ?float
    {
        return $this->vat_rate;
    }

    public function setVatRate(float $vat_rate): self
    {
        $this->vat_rate = $vat_rate;

        return $this;
    }

    public function getVatAmount(): ?float
    {
        return $this->vat_amount;
    }

    public function setVatAmount(float $vat_amount): self
    {
        $this->vat_amount = $vat_amount;

        return $this;
    }

    public function isIsCustom(): ?bool
    {
        return $this->is_custom;
    }

    public function setIsCustom(bool $is_custom): self
    {
        $this->is_custom = $is_custom;

        return $this;
    }

    public function getBasedOn(): ?float
    {
        return $this->based_on;
    }

    public function setBasedOn(float $based_on): self
    {
        $this->based_on = $based_on;

        return $this;
    }
}
