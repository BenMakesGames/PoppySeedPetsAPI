<?php

namespace App\Entity;

use App\Repository\SurveyQuestionAnswerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SurveyQuestionAnswerRepository::class)
 */
class SurveyQuestionAnswer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=SurveyQuestion::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"surveyQuestionAnswer"})
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     * @Groups({"surveyQuestionAnswer"})
     */
    private $answer;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdOn;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?SurveyQuestion
    {
        return $this->question;
    }

    public function setQuestion(?SurveyQuestion $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeImmutable $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }
}
