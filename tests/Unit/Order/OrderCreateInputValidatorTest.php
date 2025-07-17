<?php

namespace App\Tests\Unit\Order;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Dto\Customer\Order\Create\OrderItemInput;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderCreateInputValidatorTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidationSucceedsWithValidData(): void
    {
        $item1 = $this->createOrderItemInput(1, 2);
        $item2 = $this->createOrderItemInput(2, 3);

        $dto = new OrderCreateInput();
        $dto->businessId = 123;
        $dto->pickUpDate = '2025-08-01';
        $dto->items = [$item1, $item2];

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testValidationFailsWhenNoItems(): void
    {
        $orderInput = new OrderCreateInput();
        $orderInput->businessId = 123;
        $orderInput->pickUpDate = '2025-08-01';
        $orderInput->items = [];

        $violations = $this->validator->validate($orderInput);

        $this->assertGreaterThan(0, count($violations));
        $this->assertSame('This collection should contain 1 element or more.', $violations->get(0)->getMessage());
    }

    public function testValidationFailsWhenDuplicateArticleIds(): void
    {
        $item1 = $this->createOrderItemInput(1, 2);
        $item2 = $this->createOrderItemInput(1, 3);

        $orderInput = new OrderCreateInput();
        $orderInput->businessId = 123;
        $orderInput->pickUpDate = '2025-08-01';
        $orderInput->items = [$item1, $item2];

        $violations = $this->validator->validate($orderInput);

        $this->assertGreaterThan(0, count($violations));
        $this->assertSame('Duplicate articleId 1 found in order items.', $violations->get(0)->getMessage());
    }

    private function createOrderItemInput(int $articleId, int $quantity): OrderItemInput
    {
        $item = new OrderItemInput();
        $item->articleId = $articleId;
        $item->quantity = $quantity;

        return $item;
    }
}
