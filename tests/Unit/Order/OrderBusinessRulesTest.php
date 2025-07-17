<?php

namespace App\Tests\Unit\Order;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Dto\Customer\Order\Create\OrderItemInput;
use App\Exception\BusinessLogicException;
use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Service\Article\ArticleFinder;
use App\Service\Business\BusinessFinder;
use App\Service\Order\OrderBusinessRulesChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class OrderBusinessRulesTest extends KernelTestCase
{
    use Factories;

    public function testThrowsExceptionWhenOrderContainsArticlesFromMultipleBusinesses(): void
    {
        // Arrange
        $business1 = BusinessFactory::createOne();
        $business2 = BusinessFactory::createOne();

        $article1 = ArticleFactory::createOne(['business' => $business1]);
        $article2 = ArticleFactory::createOne(['business' => $business2]);

        $businessFinder = $this->createMock(BusinessFinder::class);
        $businessFinder->method('find')->with($business1->getId())->willReturn($business1);

        $articleFinder = $this->createMock(ArticleFinder::class);

        $articleFinder->method('find')->willReturnMap([
            [$article1->getId(), $article1],
            [$article2->getId(), $article2],
        ]);

        $checker = new OrderBusinessRulesChecker($businessFinder, $articleFinder);

        $orderInput = new OrderCreateInput();
        $orderInput->businessId = $business1->getId();
        $orderInput->pickUpDate = (new \DateTime())->format('Y-m-d');
        $orderInput->items = [
            $this->createOrderItemInput($article1),
            $this->createOrderItemInput($article2),
        ];

        // Assert
        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('An article is not from the business.');

        // Act
        $checker->validateOrderInput($orderInput);
    }

    private function createOrderItemInput($article): OrderItemInput
    {
        $item = new OrderItemInput();
        $item->articleId = $article->getId();
        $item->quantity = 1;

        return $item;
    }
}
