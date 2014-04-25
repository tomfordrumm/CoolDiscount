<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 05.04.14
 * Time: 1:37
 */
class TD_CoolDiscount_Model_Observer extends Mage_Core_Model_Abstract{

    public function saveDiscount($observer){
        $customer = $observer->getEvent()->getCustomer();
        $discount = Mage::app()->getRequest()->getParam('cooldiscount');
        $model = Mage::getModel('td_cooldiscount/discount');
        $loaded = $model->loadDiscount($customer->getId());
        if ($loaded){
            if ($discount == ''){
                $loaded->delete();
            } else{
                $loaded->setDiscount($discount);
                $loaded->save();
            }

        } elseif ($discount != '') {
            $model->setCustomerId($customer->getId());
            $model->setDiscount($discount);
            $model->save();
        }


        return $this;
    }

    public function setDiscount($observer){
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $discountModel = Mage::getModel('td_cooldiscount/discount')->loadDiscount($customerId);
        $quote = $observer->getEvent()->getQuote();
        $quoteid = $quote->getId();
        $gt = $quote->getGrandTotal();
        $discountAmount =  0;
        $accumulate = Mage::getModel('td_cooldiscount/accumulative')->loadByCustomerId($customerId);
        if ($discountModel) {
            $gt = $quote->getGrandTotal();
            $percent = $discountModel->getDiscount() / 100;
            $discountAmount = $gt * ($percent );
        } elseif ($accumulate) {
            $acc = $accumulate->getOrdersSumm();
            if ($acc >= 50000 and $acc <= 100000){
                $percent = 0.02;
                $discountAmount = $gt * $percent;
            }
            if ($acc > 100000 and $acc <= 200000){
                $percent = 0.04;
                $discountAmount = $gt * $percent;
            }
            if ($acc > 200000){
                $percent = 0.06;
                $discountAmount = $gt * $percent;
            }
        } else {
            if ($gt >= 50000 and $gt <= 100000){
                $percent = 0.02;
                $discountAmount = $gt * $percent;
            }
            if ($gt > 100000 and $gt <= 200000){
                $percent = 0.04;
                $discountAmount = $gt * $percent;
            }
            if ($gt > 200000){
                $percent = 0.06;
                $discountAmount = $gt * $percent;
            }
        }
        if ($quoteid and $discountAmount != 0) {


            if ($discountAmount > 0) {
                $total = $quote->getBaseSubtotal();
                $quote->setSubtotal(0);
                $quote->setBaseSubtotal(0);

                $quote->setSubtotalWithDiscount(0);
                $quote->setBaseSubtotalWithDiscount(0);

                $quote->setGrandTotal(0);
                $quote->setBaseGrandTotal(0);


                $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');
                foreach ($quote->getAllAddresses() as $address) {

                    $address->setSubtotal(0);
                    $address->setBaseSubtotal(0);

                    $address->setGrandTotal(0);
                    $address->setBaseGrandTotal(0);

                    $address->collectTotals();

                    $quote->setSubtotal((float)$quote->getSubtotal() + $address->getSubtotal());
                    $quote->setBaseSubtotal((float)$quote->getBaseSubtotal() + $address->getBaseSubtotal());

                    $quote->setSubtotalWithDiscount(
                        (float)$quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                    );
                    $quote->setBaseSubtotalWithDiscount(
                        (float)$quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                    );

                    $quote->setGrandTotal((float)$quote->getGrandTotal() + $address->getGrandTotal());
                    $quote->setBaseGrandTotal((float)$quote->getBaseGrandTotal() + $address->getBaseGrandTotal());

                    $quote->save();

                    $quote->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                        ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                        ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->save();


                    if ($address->getAddressType() == $canAddItems) {
                        //echo $address->setDiscountAmount; exit;
                        $address->setSubtotalWithDiscount((float)$address->getSubtotalWithDiscount() - $discountAmount);
                        $address->setGrandTotal((float)$address->getGrandTotal() - $discountAmount);
                        $address->setBaseSubtotalWithDiscount((float)$address->getBaseSubtotalWithDiscount() - $discountAmount);
                        $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() - $discountAmount);
                        if ($address->getDiscountDescription()) {
                            $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                            $address->setDiscountDescription($address->getDiscountDescription() . ', Custom Discount '.($percent*100).'%');
                            $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                        } else {
                            $address->setDiscountAmount(-($discountAmount));
                            $address->setDiscountDescription('Custom Discount '.($percent*100).'%');
                            $address->setBaseDiscountAmount(-($discountAmount));
                        }
                        $address->save();
                    }

                }

                foreach ($quote->getAllItems() as $item) {
                    //We apply discount amount based on the ratio between the GrandTotal and the RowTotal
                    $rat = $item->getPriceInclTax() / $total;
                    $ratdisc = $discountAmount*$rat;
                    $item->setDiscountAmount(($item->getDiscountAmount()+$ratdisc) * $item->getQty());
                    $item->setBaseDiscountAmount(($item->getBaseDiscountAmount()+$ratdisc) * $item->getQty())->save();
                }
            }
        }

        return $this;
    }

    public function accumulative($observer){
        $order= $observer->getEvent()->getOrder();
        if ($order->getStatus() == 'processing') {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $model = Mage::getModel('td_cooldiscount/accumulative');
            $loaded = $model->loadByCustomerId($customerId);
            if ($loaded) {
                $oldSumm = $loaded->getOrdersSumm();
                Mage::log($oldSumm);
                Mage::log($oldSumm + $order->getGrandTotal());
                $loaded->setOrdersSumm($oldSumm + $order->getGrandTotal());
                $loaded->save();
            } else {
                $model->setCustomerId($customerId);
                $model->setOrdersSumm($order->getGrandTotal());
                $model->save();
            }
        }

        return $this;
    }
}