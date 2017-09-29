<?php
namespace Pylon\Erp\Api;
 
interface UpdateInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function updateCategories();
    //public function deleteAllCategories();
    public function updateProducts();
    //public function updateExistingProducts($productId);
    //public function getCollectionProducts();
    public function updateOrders();
    public function updateCustomers();
    
       
    
    
}