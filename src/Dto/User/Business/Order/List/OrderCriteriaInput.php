<?php

namespace App\Dto\User\Business\Order\List;

use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderCriteriaInput
{
    #[Assert\Range(min: 1)]
    public int $page = 1;

    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 20;

    #[Context([
        DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
    ])]
    #[Assert\Type(DateTimeImmutable::class)]
    public ?DateTimeImmutable $pickUpDate = null;

    #[Context([
        DateTimeNormalizer::FORMAT_KEY => 'Y-m-d-H:i:s',
    ])]
    #[Assert\Type(DateTimeImmutable::class)]
    public ?DateTimeImmutable $pickUpFrom = null;

    #[Context([
        DateTimeNormalizer::FORMAT_KEY => 'Y-m-d-H:i:s',
    ])]
    #[Assert\Type(DateTimeImmutable::class)]
    public ?DateTimeImmutable $pickUpTo = null;

    #[Context([
        AbstractNormalizer::FILTER_BOOL => true,
    ])]
    public ?bool $isPickedUp = null;

    #[Context([
        AbstractNormalizer::FILTER_BOOL => true,
    ])]
    public ?bool $isValidatedByBusiness = null;

    #[Context([
        AbstractNormalizer::FILTER_BOOL => true,
    ])]
    public ?bool $isValidatedByCustomer = null;

    #[Assert\Length(max: 20)]
    public ?string $customerFirstName = null;

    #[Assert\Length(max: 20)]
    public ?string $customerLastName = null;

    #[Assert\Choice(choices: ['pickUpDate', 'customerFirstName', 'customerLastName'], message: 'Invalid sort field')]
    public ?string $orderBy = null;

    #[Assert\Choice(choices: ['asc', 'desc'], message: 'Invalid sort direction')]
    public ?string $orderDir = 'asc';
}
