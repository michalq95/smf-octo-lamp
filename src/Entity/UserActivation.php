<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Validator\Constraints as Assert;


#[ApiResource(
    operations: [
        new Post(
            uriTemplate: "/users/activate",

        )
    ]
)]
class UserActivation
{
    #[Assert\NotBlank()]
    public $activationToken;
}