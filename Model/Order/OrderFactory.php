<?php
/**
 * Created by PhpStorm.
 * User: vuki
 * Date: 3/13/14
 * Time: 2:03 PM
 */

namespace Eltrino\EbayBundle\Model\Order;

use Eltrino\EbayBundle\Entity\Customer;
use Eltrino\EbayBundle\Entity\Order;
use Eltrino\EbayBundle\Entity\OrderItem;
use Eltrino\EbayBundle\Model\Customer\CustomerAddress;
use SimpleXMLElement;
use Doctrine\Common\Collections\ArrayCollection;

use Eltrino\EbayBundle\Model\OrderItem\ItemInfo;
use Eltrino\EbayBundle\Model\OrderItem\Transaction;

class OrderFactory
{
    /**
     * Create Order
     */
    public function createOrder(SimpleXMLElement $data)
    {
        $ebayOrderId         = (string) $data->OrderID;
        $buyerUserId         = (string) $data->BuyerUserID;
        $orderStatus         = (string) $data->OrderStatus;
        $amountPaid          = (string) $data->AmountPaid;
        $currencyId          = (string) $data->AmountPaid['currencyID'];
        $salesTaxPercent     = (string) $data->ShippingDetails->SalesTax->SalesTaxPercent;
        $salesTaxAmount      = (string) $data->ShippingDetails->SalesTax->SalesTaxAmount;
        $shippingService     = (string) $data->ShippingDetails->ShippingServiceOptions->ShippingService;
        $shippingServiceCost = (string) $data->ShippingDetails->ShippingServiceOptions->ShippingServiceCost;
        $paymentMethods      = (string) $data->PaymentMethods;
        $sellerUserId        = (string) $data->SellerUserID;
        $sellerEmail         = (string) $data->SellerEmail;
        $subtotal            = (string) $data->Subtotal;
        $total               = (string) $data->Total;

        $customer = $this->prepareCustomer($data);
        $items    = $this->prepareOrderItems($data->TransactionArray->Transaction);

        $shipping     = new Shipping($salesTaxPercent, $salesTaxAmount, $shippingService, $shippingServiceCost);
        $payment      = new Payment($amountPaid, $currencyId, $paymentMethods);
        $orderDetails = new OrderDetails($orderStatus, $subtotal, $total, $sellerEmail, $payment, $shipping);

        return new Order($ebayOrderId, $buyerUserId, $sellerUserId, $orderDetails, $items, $customer);
    }

    private function prepareOrderItems($transactionItems)
    {
        $orderItems = new ArrayCollection();

        foreach ($transactionItems as $transactionItem) {
            $buyerEmail            = (string) $transactionItem->Buyer->Email;
            $itemId                = (string) $transactionItem->Item->ItemID;
            $itemSite              = (string) $transactionItem->Item->Site;
            $itemTitle             = (string) $transactionItem->Item->Title;
            $sku                   = (string) $transactionItem->Item->SKU;
            $conditionDisplayName  = (string) $transactionItem->Item->ConditionDisplayName;
            $quantityPurchased     = (string) $transactionItem->QuantityPurchased;
            $transactionId         = (string) $transactionItem->TransactionID;
            $transactionPrice      = (string) $transactionItem->TransactionPrice;
            $currencyId            = (string) $transactionItem->TransactionPrice['currencyID'];
            $totalTaxAmount        = (string) $transactionItem->Taxes->TotalTaxAmount;
            $orderLineItemId       = (string) $transactionItem->OrderLineItemID;

            $itemInfo    = new ItemInfo($itemId, $itemSite, $itemTitle, $sku, $conditionDisplayName);
            $transaction = new Transaction($transactionId, $transactionPrice, $currencyId, $totalTaxAmount);

            $orderItems->add(new OrderItem($buyerEmail, $itemInfo, $quantityPurchased, $transaction, $orderLineItemId));
        }

        return $orderItems;
    }

    private function prepareCustomer($data)
    {
        $buyerUserId     = (string) $data->BuyerUserID;
        $name            = (string) $data->ShippingAddress->Name;
        $street1         = (string) $data->ShippingAddress->Street1;
        $street2         = (string) $data->ShippingAddress->Street2;
        $city            = (string) $data->ShippingAddress->CityName;
        $stateOrProvince = (string) $data->ShippingAddress->StateOrProvince;
        $countryName     = (string) $data->ShippingAddress->CountryName;
        $phone           = (string) $data->ShippingAddress->Phone;
        $postalCode      = (string) $data->ShippingAddress->PostalCode;

        $customerAddress = new CustomerAddress($street1, $street2, $city, $stateOrProvince, $countryName, $postalCode);

        return new Customer($buyerUserId, $name, $phone, $customerAddress);
    }
}