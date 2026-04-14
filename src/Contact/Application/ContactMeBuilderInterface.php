<?php

declare(strict_types=1);

namespace MyEspacio\Contact\Application;

use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Exceptions\InvalidEmailException;

interface ContactMeBuilderInterface
{
    /**
     * @throws InvalidEmailException
     */
    public function build(DataSet $data): ContactMeMessage;
}
