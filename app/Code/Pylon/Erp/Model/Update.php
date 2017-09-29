<?php

namespace Pylon\Erp\Model;

use Pylon\Erp\Api\UpdateInterface;
use \Magento\Framework\App\Bootstrap;

include('app/bootstrap.php');

use PDO;
//use \Magento\CatalogInventory\Api\StockRegistryInterface;

class Update implements UpdateInterface {

    private $pylon_codes;
    private $categoryLinkManagement;
    protected $stockRegistry;
    private $pylon_product_codes;
    protected $bootstrap;
    protected $storeManager;
    protected $state;
    protected $category;
    protected $resource;
    protected $categoryFactory;
    protected $catalogConfig;
    protected $productRepository;
    protected $productFactory;
    protected $_objectManager;
    protected $productCollectionFactory;
    protected $entityManager;
    protected $customerFactory;
    protected $addressFactory;
    protected $resourceConnection;
    public function __construct(
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\App\State $state,
            \Magento\Catalog\Model\Category $category,
            \Magento\Framework\App\ResourceConnection $resourceConnection,
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
            \Magento\Catalog\Model\Config $catalogConfig,
            \Magento\Catalog\Model\ProductRepository $productRepository,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Magento\Framework\EntityManager\EntityManager $entityManager,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\Indexer\Model\IndexerFactory $_indexerFactory,
            \Magento\Indexer\Model\Indexer\CollectionFactory $_indexerCollectionFactory,
            \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
            \Magento\Framework\Api\SearchCriteriaInterface $criteria,
            \Magento\Framework\Api\Search\FilterGroup $filterGroup,
            \Magento\Framework\Api\FilterBuilder $filterBuilder,
            \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
            \Magento\Catalog\Model\Product\Visibility $productVisibility,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Customer\Model\AddressFactory $addressFactory
            ) {
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->category = $category;
        $this->resourceConnection = $resourceConnection;
        $this->categoryFactory = $categoryFactory;
        $this->catalogConfig = $catalogConfig;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->_objectManager = $objectManager;
        $this->stockRegistry = $stockRegistry;
        $this->entityManager = $entityManager;
        $this->_indexerFactory = $_indexerFactory;
        $this->_indexerCollectionFactory = $_indexerCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->searchCriteria = $criteria;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->getProductCollection();
        $this->reIndex();
    }
    
    
    /*
     * Function for adding categories to store
     * 
     * git commit
     *      * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit

     */
    

    public function updateCategories() {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        
        $objectManager = $this->createObjectManager();
        $url = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $url->get('\Magento\Store\Model\StoreManagerInterface');
        /// Get Website ID
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        echo 'websiteId: ' . $websiteId . " ";
        /// Get Store ID
        $store = $storeManager->getStore();
        $storeId = $store->getStoreId();
        echo 'storeId: ' . $storeId . " ";
        /// Get Root Category ID
        $rootNodeId = $store->getRootCategoryId();
        echo 'rootNodeId: ' . $rootNodeId . " ";
        /// Get Root Category
        $cat_info = $this->category->load($rootNodeId);
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_category_entity_varchar');
        $tableName2 = $this->resourceConnection->getTableName('catalog_category_entity');
        $categories = $decoded['Items']; // Category Names 
        $last_id = "SELECT entity_id FROM " . $tableName2 . " ORDER BY entity_id DESC LIMIT 1";
        $execution = $connection->fetchAll($last_id);
        $entity_id = $execution[0]['entity_id'] + 1;
        print_r($entity_id);
        if (!file_exists("c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/categories.json")) {
            $this->createJson(false, true);
        }
        $arr2 = json_decode(file_get_contents("c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/categories.json"), true);
        $this->pylon_codes = $arr2;
        foreach ($categories as $cat) {
            if (array_key_exists($cat['id'], $this->pylon_codes)) {
                echo $cat['id'] . " This Category has already been added " . $cat['id'] . "\n";
            } else {
                $cat_code = $cat['id'];
                echo "Added Category " . $cat['id'] . "\n";
                $this->pylon_codes[$cat_code] = $entity_id;
                $name = ucfirst($cat['name']);
                $url = strtolower($cat['name']);
                $cleanurl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));
                $categoryTmp = $this->categoryFactory->create();
                $categoryTmp->setName($name);
                $categoryTmp->setIsActive(true);
                $categoryTmp->setUrlKey($cleanurl);
                $categoryTmp->setData($cat['name'], $cat['name']);
                if ($cat['parent_id'] == NULL) {
                    $categoryTmp->setParentId($rootCat->getId());
                    $parent_name = $cat['parent_name'];
                    $categoryTmp->setPath($rootCat->getPath());
                } else {
                    $parent_name = $cat['parent_name'];
                    $parent_id = "SELECT entity_id FROM " . $tableName . " WHERE value LIKE '%$parent_name%'";
                    $getParentId = $connection->fetchAll($parent_id);
                    $categoryTmp->setParentId($getParentId[0]['entity_id']);
                    $firstPar = $getParentId[0]['entity_id'];
                    $secondPar = $rootCat->getPath();
                    $wholePath = "$firstPar/$secondPar";
                    $categoryTmp->setPath($wholePath);
                }
                $mediaAttribute = array('image', 'small_image', 'thumbnail');
                $categoryTmp->setImage('/m2.png', $mediaAttribute, true, false); // Path pub/meida/catalog/category/m2.png
                $categoryTmp->setStoreId($storeId);
                $categoryTmp->save();
                $entity_id++;
            }
        }
        file_put_contents('c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/categories.json', json_encode($this->pylon_codes));
        $this->getCategoryCollection();
    }

    ///test
        ///test
    ///test
    ///test
    ///test
    ///test

    
    
    /*
    
    public function createObjectManager(){
        $bootstrap = Bootstrap::create(BP, $_SERVER);
        $objectManager = $bootstrap->getObjectManager();
        //$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        
        $appState = $objectManager->get('\Magento\Framework\App\State');
        $appState->setAreaCode('frontend');
        return $objectManager;
     *      * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
     * git commit
    }
     * 
     */
    
    
    
    
    /*
     * Function for updating existing products
     */
    
    
    
    private function updateExistingProducts($productsIds, $productsToUpdate) {
        $collection = $this->productCollectionFactory->create()->addIdFilter($productsIds);
        foreach ($collection->getItems() as $product) {
            try {
                $productSku = $product->getSku();
                $dbProduct = $productsToUpdate[$productSku];
                //print_r($dbProduct);
                $inventoryQty = ($dbProduct["stock_status"] > 0) ? $dbProduct["stock_status"] : 0;
                $isInStock = ($inventoryQty > 0) ? 1 : 0;
                $stockItem = $this->stockRegistry->getStockItemBySku($dbProduct["sku"]);
                $stockItem->setQty($inventoryQty);
                $stockItem->setIsInStock($isInStock);
                $this->stockRegistry->updateStockItemBySku($dbProduct["sku"], $stockItem);
                $stockItem->save();
                echo $dbProduct['sku'] ."Product Updated Succesfully \n";
            } catch (Exception $e) {
                $e->getMessage();
            }
        }
    }
    /*
     * Function for getting category collection from specific store
     */
    
    
    public function getCategoryCollection(){
        $categoryFactory = $this->categoryFactory->create();
        $categoryFactory->addAttributeToSelect('*')->setStore($this->storeManager->getStore());
        $json_to_return = json_encode($categoryFactory);
        print_r($json_to_return);
    }
    
    /*
     * Function for getting product collection from catalog
     */
    

    public function getProductCollection(){
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $json_to_return = json_encode($collection);
        print_r($json_to_return);
    }
    /*
     * Creates Json to save product or category codes etc.
     */
    private function createJson($products, $categories){
        $emptyArray = array();
        if ($products){
            file_put_contents("c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/products.json", json_encode($emptyArray));
        }
        if ($categories){
            file_put_contents("c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/categories.json", json_encode($emptyArray));
        }
    }    
    
    
    /*
     * Function for adding products
     */
    
    public function updateProducts() {
        $catalogConfig = $this->_objectManager->create('Magento\Catalog\Model\Config');
        $attributeSetId = $catalogConfig->getAttributeSetId(4, 'Default');
        $productsToUpdate = [];
        $productsIds = [];
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        $products = $decoded['Items'];
        $url = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $url->get('\Magento\Store\Model\StoreManagerInterface');
        $websiteId = $storeManager->getWebsite()->getWebsiteId();
        if (!file_exists("c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/products.json")) {
            $this->createJson(true,false);
        }
        $product_array = json_decode(file_get_contents("c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/products.json"), true);
        $decoded_cat = json_decode(file_get_contents('c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/categories.json'), true);
        $this->pylon_codes = $decoded_cat;
        $this->pylon_product_codes = $product_array;
        print_r($product_array);
        $products_per_family = array();
        $products_to_add = 0;
        foreach($products as $prod){
            if (in_array($prod['code'], $this->pylon_product_codes)){
                echo $prod['code'] . " This Product has already been added \n";
                $productToUpdateId = array_search($prod['code'], $this->pylon_product_codes);
                $tempProduct = $this->productRepository->getById($productToUpdateId);
                $productsIds = $tempProduct->getId();
                if ($tempProduct->getSku()){
                    $productsToUpdate[$tempProduct->getSku()] = $prod;
                    $this->updateExistingProducts($productsIds, $productsToUpdate);
                }
            }
            else{
                $_product = $this->productFactory->create();
                $_product->setWebsiteIds(array($websiteId));
                $_product->setAttributeSetId(4);
                $_product->setTypeId('simple');
                $_product->setCreatedAt(strtotime('now')); //product creation time
                $_product->setName($prod['description']); // Product Name
                $_product->setSku($prod['sku']);
                if ($prod['weight'] != NULL) {
                    $_product->setProductHasWeight(1);
                    $_product->setWeight($prod['weight']);
                } else {
                    $_product->setProductHasWeight(0);
                }

                $_product->setStatus(1);
                $prod_cat = $prod['item_categories'];

                if ($prod_cat != NULL) {
                    $cats = explode(';', $prod_cat);
                    
                    foreach($cats as $cat){
                        $prod_cat_padded = sprintf("%06d", $cat);
                        $category_Ids [] = $this->pylon_codes[$prod_cat_padded];
                    }
                    
                    $_product->setCategoryIds($category_Ids);
                }

                $_product->setTaxClassId(0); //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                $_product->setVisibility(4); //catalog and search visibility
                $_product->setManufacturer(28); //manufacturer id
                $_product->setColor(24);
                $_product->setNewsFromDate('06/26/2014'); //product set as new from
                $_product->setNewsToDate('06/30/2014'); //product set as new to
                $_product->setCountryOfManufacture('GR'); //country of manufacture (2-letter country code)
                $_product->setPrice($prod['original_price']); //price in form 11.22
                $_product->setCost(22.33); //price in form 11.22
                $_product->setSpecialPrice(00.44); //special price in form 11.22
                $_product->setSpecialFromDate('06/1/2016'); //special price from (MM-DD-YYYY)
                $_product->setSpecialToDate('06/30/2014'); //special price to (MM-DD-YYYY)
                $_product->setMsrpEnabled(1); //enable MAP
                $_product->setMsrpDisplayActualPriceType(1); //display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
                $_product->setMsrp(99.99); //Manufacturer's Suggested Retail Price
                $_product->setMetaTitle('test meta title 2');
                $_product->setMetaKeyword('test meta keyword 2');
                $_product->setMetaDescription('test meta description 2');
                $_product->setDescription('This is a long description');
                $_product->setShortDescription('This is a short description');
                $_product->setCustomAttribute('Pylon_Codes', $prod['code']);
                $_product->setCustomAttribute('Family_Code', $prod['family_code']);
                $_product->setStockData(array(
                    'use_config_manage_stock' => 0, //'Use config settings' checkbox
                    'manage_stock' => 1, //manage stock
                    'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                    'max_sale_qty' => 3, //Maximum Qty Allowed in Shopping Cart
                    'is_in_stock' => 1 , //Stock Availability
                    'qty' => $prod['stock_status'] //qty
                        )
                );
                $_product->save();
                $this->pylon_product_codes[$_product->getId()] = $prod['code'];
                if ($prod['family_code'] && $prod['code']){
                    $products_per_family[$prod['family_code']][] = $prod['code'];
                }
                echo $_product->getId() ."Added product" ."\n";
                $products_to_add++;    
            }
        }
        $this->createRelations($products_per_family);
        
        if ($products_to_add>0){
            $addToJson = json_encode($this->pylon_product_codes);
            file_put_contents('c:/wamp64/www/magento2/app/Code/Pylon/Erp/Model/products.json', $addToJson);
        }
        $this->reIndex();
        $this->getProductCollection();
    }
    
    /*
     * Function for creating relations between products
     */
    
    
    private function createRelations ($products_per_family){
        
        $linkDataAll = array();
        $productObject = $this->_objectManager->get('Magento\Catalog\Model\Product');
        foreach ($products_per_family as $family_code => $family_code_products){
            
            
            $product = $productObject->loadByAttribute ('Family_Code',$family_code);
            echo "Id of the product to connect" .$product->getId() ."\n";
            
            foreach ($family_code_products as $product_code){
                $linkedProduct = $productObject->loadByAttribute('Pylon_Codes', $product_code);
                if ($linkedProduct && $linkedProduct->getSku()){
                    echo "Product to connect" .$product_code ."\n";
                    echo "Id of product " .$linkedProduct->getId() . "\n";
                    $productLinks = $this->_objectManager->create('Magento\Catalog\Api\Data\ProductLinkInterface');
                    
                    $linkData = $productLinks //Magento\Catalog\Api\Data\ProductLinkInterface
                        ->setSku($product->getSku())
                        ->setLinkedProductSku($linkedProduct->getSku())
                        ->setLinkType("related");
                    $linkDataAll[] = $linkData;
                    echo "Product". $linkedProduct->getId() ." linked succesfully\n";
                }
            }
            $product->setProductLinks($linkDataAll);
            if($linkDataAll){
                $product->save();
            }
            $linkDataAll = array();
            echo "Is empty?" .empty($linkDataAll)."\n";
        }
        
    }

    /*
     * Function for reindexing
     */
    
    public function reIndex(){
        $indexer = $this->_indexerFactory->create();
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $ids = $indexerCollection->getAllIds();

        foreach ($ids as $id){
            $idx = $indexer->load($id);
            $idx->reindexRow($id);
        }
    }
    
    
    public function updateOrders(){
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        print_r($decoded);
    }
    
    /*
     * Function for adding a customer or updating existed
     */
    
    
    public function updateCustomers(){
        $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        
        
        $customer->setEmail("email@domain.com"); 
        $customer->setFirstname("First Name");
        $customer->setLastname("Last name");
        $customer->setPassword("password");
        
        $customer->save();
        $customer->sendNewAccountEmail();
        
        $address = $this->addressFactory->create();
        $address->setCustomerId($customer->getId())
        ->setFirstname(FirstName)
 
        ->setLastname(LastName)

        ->setCountryId(US)

        ->setPostcode(85001)

        ->setCity(Pheonix)

        ->setTelephone(1234567890)

        ->setFax(123456789)

        ->setCompany(Company)

        ->setStreet(Street)

        ->setIsDefaultBilling('1')

        ->setIsDefaultShipping('1')

        ->setSaveInAddressBook('1');
        $address->save();
                
        
    }

    

}
