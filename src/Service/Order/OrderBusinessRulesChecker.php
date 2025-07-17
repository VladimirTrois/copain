<?php

namespace App\Service\Order;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Exception\BusinessLogicException;
use App\Service\Article\ArticleFinder;
use App\Service\Business\BusinessFinder;

class OrderBusinessRulesChecker
{
    public function __construct(
        private BusinessFinder $businessFinder,
        private ArticleFinder $articleFinder,
    ) {
    }

    public function validateOrderInput(OrderCreateInput $orderInput): void
    {
        $businessId = $orderInput->businessId;

        // Find Business
        $business = $this->businessFinder->find($businessId);

        if (null !== $business->isDeleted() && $business->isDeleted()) {
            throw new BusinessLogicException('This business is not accepting orders at the moment.');
        }

        // Make sure all articles are from business
        foreach ($orderInput->items as $item) {
            $articleId = $item->articleId;
            $article = $this->articleFinder->find($articleId);
            if (!$business->isArticleFromBusiness($article)) {
                throw new BusinessLogicException('An article is not from the business.');
            }
        }
    }
}
