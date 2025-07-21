<?php

namespace App\Tests\Unit\Order;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Dto\Customer\Order\Create\OrderItemInput;
use App\Dto\Customer\Order\Update\OrderUpdateInput;
use App\Entity\Article;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Exception\BusinessLogicException;
use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\CustomerFactory;
use App\Mapper\Customer\Order\OrderInputMapper;
use App\Service\Article\ArticleFinder;
use App\Service\Business\BusinessFinder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class OrderInputMapperTest extends KernelTestCase
{
    use Factories;

    public function testMapsOrderCreateInputToOrderEntity(): void
    {
        // Arrange
        $customer = CustomerFactory::createOne();
        $business = BusinessFactory::createOne();
        $article = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $articleFinder = $this->createMock(ArticleFinder::class);
        $articleFinder->method('find')
            ->with($article->getId())
            ->willReturn($article);

        $businessFinder = $this->createMock(BusinessFinder::class);
        $businessFinder->method('find')
            ->with($business->getId())
            ->willReturn($business);

        $mapper = new OrderInputMapper($articleFinder, $businessFinder);

        $itemInput = $this->createOrderItemInput($article, 2);

        $this->assertNotNull($business->getId());
        $input = new OrderCreateInput();
        $input->businessId = $business->getId();
        $input->pickUpDate = (new \DateTime())->format('Y-m-d');
        $input->items = [$itemInput];

        // Act
        $order = $mapper->mapToEntity($input, $customer);

        // Assert
        $this->assertSame($customer->getId(), $order->getCustomer()->getId());
        $this->assertSame($business->getId(), $order->getBusiness()->getId());
        $this->assertNotNull($order->getOrderItems()[0]);
        $this->assertEquals($itemInput->quantity, $order->getOrderItems()[0]->getQuantity());
        $this->assertEquals($article->getId(), $order->getOrderItems()[0]->getArticle()->getId());
    }

    public function testUpdatesExistingOrderWithNewItems(): void
    {
        $customer = CustomerFactory::createOne();
        $business = BusinessFactory::createOne();
        $oldArticle = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $order = new Order();
        $order->setCustomer($customer);
        $order->setBusiness($business);
        $orderItem = new OrderItem();
        $orderItem->setArticle($oldArticle);
        $orderItem->setQuantity(2);
        $order->addOrderItem($orderItem);

        // Update input
        $newArticle = ArticleFactory::createOne([
            'business' => $business,
        ]);
        $itemInput = $this->createOrderItemInput($newArticle, 3);

        $input = new OrderUpdateInput();
        $input->pickUpDate = '2025-08-15';
        $input->items = [$itemInput];

        $articleFinder = $this->createMock(ArticleFinder::class);
        $articleFinder->method('find')
            ->with($newArticle->getId())
            ->willReturn($newArticle);

        $businessFinder = $this->createMock(BusinessFinder::class);

        $mapper = new OrderInputMapper($articleFinder, $businessFinder);

        // Act
        $updatedOrder = $mapper->mapToExistingEntity($order, $input);

        // Assert
        $this->assertEquals(new \DateTime('2025-08-15'), $updatedOrder->getPickUpDate());
        $this->assertCount(1, $updatedOrder->getOrderItems());
        $this->assertNotNull($updatedOrder->getOrderItems()->get(1));
        $this->assertSame($newArticle->getId(), $updatedOrder->getOrderItems()->get(1)->getArticle()->getId());
        $this->assertEquals(3, $updatedOrder->getOrderItems()->get(1)->getQuantity());
    }

    public function testThrowsExceptionWhenOrderContainsArticlesFromMultipleBusinesses(): void
    {
        // Arrange
        $customer = CustomerFactory::createOne();
        $business1 = BusinessFactory::createOne();
        $business2 = BusinessFactory::createOne();

        $article1 = ArticleFactory::createOne([
            'business' => $business1,
        ]);
        $article2 = ArticleFactory::createOne([
            'business' => $business2,
        ]);

        $businessFinder = $this->createMock(BusinessFinder::class);
        $businessFinder->method('find')
            ->with($business1->getId())
            ->willReturn($business1);

        $articleFinder = $this->createMock(ArticleFinder::class);

        $articleFinder->method('find')
            ->willReturnMap([[$article1->getId(), $article1], [$article2->getId(), $article2]]);

        $orderInputMapper = new OrderInputMapper($articleFinder, $businessFinder);

        $this->assertNotNull($business1->getId());
        $orderInput = new OrderCreateInput();
        $orderInput->businessId = $business1->getId();
        $orderInput->pickUpDate = (new \DateTime())->format('Y-m-d');
        $orderInput->items = [
            $this->createOrderItemInput($article1, 2),
            $this->createOrderItemInput($article2, 2),
        ];

        // Assert
        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('An article is not from the business.');

        // Act
        $orderInputMapper->mapToEntity($orderInput, $customer);
    }

    /**
     * @param Article $article
     */
    private function createOrderItemInput($article, int $quantity): OrderItemInput
    {
        $this->assertNotNull($article->getId());
        $item = new OrderItemInput();
        $item->articleId = $article->getId();
        $item->quantity = $quantity;

        return $item;
    }
}
