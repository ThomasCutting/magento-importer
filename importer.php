<?php
/**
 * Batch File Magento Importer
 * @package GSImporter
 */

require_once 'autoload.php';

/**
 * Class BatchMagentoImporter
 * --
 * The functionality of this class is to proceed the BatchFileReader.
 */
class BatchMagentoImporter
{
    /**
     * @var $import_order_array
     * This variable handles the "Import Order" array.  Consisting of what the BatchCSVProcesser returns.
     */
    private $import_order_array;

    /**
     * @var $compiled_orders
     * This variable acts as the store for all ( compiled | completely parsed ) orders.
     */
    private $compiled_orders;

    /**
     * @var $admin_user_id
     * This variable holds the identifying number of the current administrator within the magento core site.
     */
    private $admin_user_id;

    /**
     * @var $admin_user_email
     * This variable hold the admins user email.
     */
    private $admin_user_email;

    /**
     * BatchMagentoImporter constructor.
     * --
     * The constructor that handles all pre-processing
     * @param $import_orders
     * @param $mage_app_directory
     */
    public function __construct($import_orders, $mage_app_directory = null)
    {
        // process the request to include mage core ( bootstrap & mage . php )
        if ($mage_app_directory && is_dir($mage_app_directory)) {

            if (file_exists($mage_app_directory . '/bootstrap.php')) {
                // include the bootstrapped file for Magento ( this acts as a pre-processor )
                include $mage_app_directory . '/bootstrap.php';
            }
            if (file_exists($mage_app_directory . '/mage.php')) {
                // include the mage.php file
                include $mage_app_directory . '/mage.php';
            }

        }

        // make sure that the order import order array is valid & combined
        if (is_array($import_orders)) {

            // fetch the processed resource array from the method parameter.
            $this->import_order_array = $import_orders;

        }
    }

    /**
     * testMagentoConnection
     * -
     * This method should be ran as a preface of interface between any stand-alone class, and this class.
     * It must be successful for you to interact/interface between Mage::* in anyway.
     *
     * @return bool
     */
    public function testMagentoConnection()
    {
        try {
            // Try to qualify a magento model?
            $some_id = Mage::app()->getWebsite()->getId();
        } catch (Exception $e) {
            // Magento doesn't have anything to go off / include paths are off.  Return false.
            error_log('Magento Connection Failed! - ' . $e->getTraceAsString());
            return false;
        }

        // No exception was thrown - Magento is successfully included.
        return true;
    }

    /**
     * compileOrders
     * --
     * This method is front-facing and allows us to essentials "compile" orders, and build their related data structures out.
     */
    public function compileOrders()
    {
        foreach ($this->import_order_array as $order) {
            $this->compileOrder($order);
        }
    }

    /**
     * compileOrder
     * --
     * This method compiles an order ( takes all data within order, and builds out a Magento order model out with it. )
     */
    protected function compileOrder($base_order)
    {
        // get the recent website identification number
        $website_id = Mage::app()->getWebsite()->getId();

        // get the store object ( instance of? )
        $store = Mage::app()->getStore();

        // build a new quote object.
        $quote = Mage::getModel('sales/quote')->setStoreId($store->getId());

        // set order currency
        $quote->setCurrency($base_order['order_currency_code']);

        // build out the admin customer by email ( this should be held in this->admin->email? )
        $customer = Mage::getModel('customer/customer')->setWebsiteId($website_id)
            ->loadByEmail($this->admin_user_email);

        // assign the above quote to the admin
        $quote->assignCustomer($customer);

        // product loop
        // ... ( issue - look ahead feature for product gen. & add to quote feature w/ product(s)).

        // set the sales billing address
        $billingAddress = $quote->getBillingAddress()->addData([
            'customer_address_id' => '',
            'prefix' => $base_order['billing_prefix'],
            'firstname' => $base_order['billing_firstname'],
            'middlename' => $base_order['billing_middlename'],
            'lastname' => $base_order['billing_lastname'],
            'suffix' => $base_order['billing_suffix'],
            'company' => $base_order['billing_company'],
            'street' => $base_order['billing_street_full'],
            'city' => $base_order['billing_city'],
            'country_id' => $base_order['billing_country'],
            'region' => $base_order['billing_region'],
            'postcode' => $base_order['billing_postcode'],
            'telephone' => $base_order['billing_telephone'],
            'fax' => $base_order['billing_fax'],
            'vat_id' => '',
            'save_in_address_book' => 1
        ]);

        // set the sales shipping address
        $shippingAddress = $quote->getShippingAddress()->addData([
            'customer_address_id' => '',
            'prefix' => $base_order['shipping_prefix'],
            'firstname' => $base_order['shipping_firstname'],
            'middlename' => $base_order['shipping_middlename'],
            'lastname' => $base_order['shipping_lastname'],
            'suffix' => $base_order['shipping_suffix'],
            'company' => $base_order['shipping_company'],
            'street' => $base_order['shipping_street_full'],
            'city' => $base_order['shipping_city'],
            'country_id' => $base_order['shipping_country'],
            'region' => $base_order['shipping_region'],
            'postcode' => $base_order['shipping_postcode'],
            'telephone' => $base_order['shipping_telephone'],
            'fax' => $base_order['shipping_fax'],
            /*            'vat_id' => '',
                        'save_in_address_book' => 1*/
        ]);

        // collect rates, and set shipping and payment method
        $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod($base_order['shipping_method'])
                        ->setPaymentMethod($base_order['payment_method']);

        // set sales order payment
        $quote->getPayment()->importData([
           'method' => $base_order['payment_method']
        ]);

        // collect all totals and save the quote
        $quote->collectTotals()->save();

        // create order from quote
        $service = Mage::getModel('sales/service_quote',$quote);

        // submit the service quote
        $service->submitAll();

        // directly affiliate an id with the realOrderId.
        $increment_id = $service->getOrder()->getRealOrderId();

        // setup order object and
        $order = $service->getOrder();
        $order->setStatus($base_order['order_status']);

        // save the order ( w/ added & configured status )
        $order->save();

        // generate array with quote, service, order, and increment id.
        $compiled_order = [
            "quote" => $quote,
            "service" => $service,
            "order" => $order,
            "increment_id" => $increment_id
        ];

        // push the generated array.
        array_push($this->compiled_orders,$compiled_order);

        return $increment_id;
    }

    /**
     * _getOrderCreateModel
     * -
     * Magento based method that pulls in the 'adminhtml/sales_order_create' singleton model
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    /**
     * _getSession
     * -
     * Magento based method that pulls in the 'adminhtml/session_quote' singleton model
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

}

