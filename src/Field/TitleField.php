<?php

namespace Ivanstan\SymfonyRest\Field;

use Doctrine\ORM\Mapping as ORM;

trait TitleField
{
    #[ORM\Column(name: 'title', type: 'string')]
    private string $title;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
