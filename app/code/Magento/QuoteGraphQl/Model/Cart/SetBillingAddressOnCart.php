<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Set billing address for a specified shopping cart
 */
class SetBillingAddressOnCart
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @var AssignBillingAddressToCart
     */
    private $assignBillingAddressToCart;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param GetCustomerAddress $getCustomerAddress
     * @param AssignBillingAddressToCart $assignBillingAddressToCart
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        CheckCustomerAccount $checkCustomerAccount,
        GetCustomerAddress $getCustomerAddress,
        AssignBillingAddressToCart $assignBillingAddressToCart
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->getCustomerAddress = $getCustomerAddress;
        $this->assignBillingAddressToCart = $assignBillingAddressToCart;
    }

    /**
     * Set billing address for a specified shopping cart
     *
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param array $billingAddress
     * @return void
     * @throws GraphQlInputException
     * @throws GraphQlAuthenticationException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $billingAddress): void
    {
        $customerAddressId = $billingAddress['customer_address_id'] ?? null;
        $addressInput = $billingAddress['address'] ?? null;
        $useForShipping = isset($billingAddress['use_for_shipping'])
            ? (bool)$billingAddress['use_for_shipping'] : false;

        if (null === $customerAddressId && null === $addressInput) {
            throw new GraphQlInputException(
                __('The billing address must contain either "customer_address_id" or "address".')
            );
        }

        if ($customerAddressId && $addressInput) {
            throw new GraphQlInputException(
                __('The billing address cannot contain "customer_address_id" and "address" at the same time.')
            );
        }

        $addresses = $cart->getAllShippingAddresses();
        if ($useForShipping && count($addresses) > 1) {
            throw new GraphQlInputException(
                __('Using the "use_for_shipping" option with multishipping is not possible.')
            );
        }

        if (null === $customerAddressId) {
            $billingAddress = $this->quoteAddressFactory->createBasedOnInputData($addressInput);
        } else {
            $this->checkCustomerAccount->execute($context->getUserId(), $context->getUserType());
            $customerAddress = $this->getCustomerAddress->execute((int)$customerAddressId, (int)$context->getUserId());
            $billingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress($customerAddress);
        }

        $this->assignBillingAddressToCart->execute($cart, $billingAddress, $useForShipping);
    }
}
