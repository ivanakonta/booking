<?php

namespace App\Entity;

use App\Repository\RenterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RenterRepository::class)]
class Renter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modified_at = null;

    /**
     * @var Collection<int, Korisnik>
     */
    #[ORM\OneToMany(targetEntity: Korisnik::class, mappedBy: 'renter')]
    private Collection $korisniks;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Korisnik $korisnik = null;

    #[ORM\Column(length: 255)]
    private ?string $adresa = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mobitel = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?array $days = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $monriSecretKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $monriPublicKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleCalendar = null;

    /**
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(targetEntity: Vehicle::class, mappedBy: 'renter')]
    private Collection $vehicles;

    /**
     * @var Collection<int, NonWorkingDays>
     */
    #[ORM\OneToMany(targetEntity: NonWorkingDays::class, mappedBy: 'renter')]
    private Collection $nonWorkingDays;

    /**
     * @var Collection<int, WorkingDays>
     */
    #[ORM\OneToMany(targetEntity: WorkingDays::class, mappedBy: 'renter')]
    private Collection $workingDays;

    public function __construct()
    {
        $this->korisniks = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->modified_at = new \DateTimeImmutable();
        $this->vehicles = new ArrayCollection();
        $this->nonWorkingDays = new ArrayCollection();
        $this->workingDays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modified_at;
    }

    public function setModifiedAt(\DateTimeImmutable $modified_at): static
    {
        $this->modified_at = $modified_at;

        return $this;
    }

    /**
     * @return Collection<int, Korisnik>
     */
    public function getKorisniks(): Collection
    {
        return $this->korisniks;
    }

    public function addKorisnik(Korisnik $korisnik): static
    {
        if (!$this->korisniks->contains($korisnik)) {
            $this->korisniks->add($korisnik);
            $korisnik->setRenter($this);
        }

        return $this;
    }

    public function removeKorisnik(Korisnik $korisnik): static
    {
        if ($this->korisniks->removeElement($korisnik)) {
            // set the owning side to null (unless already changed)
            if ($korisnik->getRenter() === $this) {
                $korisnik->setRenter(null);
            }
        }

        return $this;
    }

    public function getKorisnik(): ?Korisnik
    {
        return $this->korisnik;
    }

    public function setKorisnik(?Korisnik $korisnik): static
    {
        $this->korisnik = $korisnik;

        return $this;
    }

    public function getAdresa(): ?string
    {
        return $this->adresa;
    }

    public function setAdresa(string $adresa): static
    {
        $this->adresa = $adresa;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getMobitel(): ?string
    {
        return $this->mobitel;
    }

    public function setMobitel(?string $mobitel): static
    {
        $this->mobitel = $mobitel;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }
    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDays(): ?array
    {
        return $this->days;
    }

    public function setDays(?array $days): static
    {
        $this->days = $days;

        return $this;
    }

    public function getMonriPublicKey(): ?string
    {
        return $this->monriSecretKey;
    }

    public function setMonriPublicKey(?string $monriSecretKey): static
    {
        $this->monriSecretKey = $monriSecretKey;

        return $this;
    }

    public function getMonriSecretKey(): ?string
    {
        return $this->monriSecretKey;
    }

    public function setMonriSecretKey(?string $monriSecretKey): static
    {
        $this->monriSecretKey = $monriSecretKey;

        return $this;
    }

    public function getGoogleCalendar(): ?string
    {
        return $this->googleCalendar;
    }

    public function setGoogleCalendar(?string $googleCalendar): static
    {
        $this->googleCalendar = $googleCalendar;

        return $this;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setRenter($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getRenter() === $this) {
                $vehicle->setRenter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NonWorkingDays>
     */
    public function getNonWorkingDays(): Collection
    {
        return $this->nonWorkingDays;
    }

    public function addNonWorkingDay(NonWorkingDays $nonWorkingDay): static
    {
        if (!$this->nonWorkingDays->contains($nonWorkingDay)) {
            $this->nonWorkingDays->add($nonWorkingDay);
            $nonWorkingDay->setRenter($this);
        }

        return $this;
    }

    public function removeNonWorkingDay(NonWorkingDays $nonWorkingDay): static
    {
        if ($this->nonWorkingDays->removeElement($nonWorkingDay)) {
            // set the owning side to null (unless already changed)
            if ($nonWorkingDay->getRenter() === $this) {
                $nonWorkingDay->setRenter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkingDays>
     */
    public function getWorkingDays(): Collection
    {
        return $this->workingDays;
    }

    public function addWorkingDay(WorkingDays $workingDay): static
    {
        if (!$this->workingDays->contains($workingDay)) {
            $this->workingDays->add($workingDay);
            $workingDay->setRenter($this);
        }

        return $this;
    }

    public function removeWorkingDay(WorkingDays $workingDay): static
    {
        if ($this->workingDays->removeElement($workingDay)) {
            // set the owning side to null (unless already changed)
            if ($workingDay->getRenter() === $this) {
                $workingDay->setRenter(null);
            }
        }

        return $this;
    }
}
