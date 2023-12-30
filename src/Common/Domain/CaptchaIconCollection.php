<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

class CaptchaIconCollection extends ArrayCollection
{
    public function __construct(array $elements)
    {
        parent::__construct();
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    public function add(mixed $element): void
    {
        if (is_array($element) === false) {
            throw new InvalidArgumentException('The element must be an array');
        }
        $captchaIcon = new CaptchaIcon(
            icon_id: intval($element['icon_id'] ?? 0),
            icon: $element['icon'] ?? '',
            name:$element['name'] ?? '',
            colour:$element['colour'] ?? ''
        );
        parent::add($captchaIcon);
    }
}
