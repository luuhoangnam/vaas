<?php

namespace App\Cashback;

class AmazonAssociatesCashbackRateResolver implements CashbackRateResolver
{
    protected $rateTable = [
        ['id' => 2864196011, 'name' => 'Gift Cards', 'rate' => 0.0],
        ['id' => 2983386011, 'name' => 'Wine', 'rate' => 0.0],
        ['id' => 468642, 'name' => 'Video Games', 'rate' => 0.01],
        ['id' => 979455011, 'name' => 'Digital Video Games', 'rate' => 0.02],
        ['id' => 193870011, 'name' => 'Computer Components', 'rate' => 0.025],
        ['id' => 3213025011, 'name' => 'Blu-ray', 'rate' => 0.025],
        ['id' => 3213027011, 'name' => 'DVD', 'rate' => 0.025],
        ['id' => 165793011, 'name' => 'Toys', 'rate' => 0.03],
        ['id' => 2102313011, 'name' => 'Amazon Devices', 'rate' => 0.04],
        ['id' => 283155, 'name' => 'Physical Books', 'rate' => 0.045],
        ['id' => 3760911, 'name' => 'Personal Care', 'rate' => 0.045],
        ['id' => 10971181011, 'name' => 'Sports & Fitness', 'rate' => 0.045],
        ['id' => 284507, 'name' => 'Kitchen', 'rate' => 0.045],
        ['id' => 15706941, 'name' => 'Automotive Tools & Equipment', 'rate' => 0.045],
        ['id' => 15684181, 'name' => 'Automotive Parts & Accessories', 'rate' => 0.045],
        ['id' => 165796011, 'name' => 'Baby Products', 'rate' => 0.045],
        ['id' => 163856011, 'name' => 'Digital Music', 'rate' => 0.05],
        ['id' => 16310101, 'name' => 'Groceries & Gourmet Food', 'rate' => 0.05],
        ['id' => 11260432011, 'name' => 'Handmade', 'rate' => 0.05],
        ['id' => 2858778011, 'name' => 'Videos', 'rate' => 0.05],
        ['id' => 706814011, 'name' => 'Outdoor', 'rate' => 0.055],
        ['id' => 228013, 'name' => 'Tools & Home Improvement', 'rate' => 0.055],
        ['id' => 172541, 'name' => 'Headphones', 'rate' => 0.06],
        ['id' => 3760911, 'name' => 'Beauty', 'rate' => 0.06],
        ['id' => 11091801, 'name' => 'Musical Instruments', 'rate' => 0.06],
        ['id' => 16310091, 'name' => 'Business & Industrial Supplies', 'rate' => 0.06],
        ['id' => 7141123011, 'name' => 'Apparel', 'rate' => 0.07],
        ['id' => 9616098011, 'name' => 'Jewelry Accessories', 'rate' => 0.07],
        ['id' => 9479199011, 'name' => 'Luggage', 'rate' => 0.07],
        ['id' => 679255011, 'name' => 'Men\'s Shoes', 'rate' => 0.07],
        ['id' => 679337011, 'name' => 'Woman\'s Shoes', 'rate' => 0.07],
        ['id' => 11403468011, 'name' => 'Handbags & Accessories', 'rate' => 0.07],
        ['id' => 6358543011, 'name' => 'Woman\s Watches', 'rate' => 0.07],
        ['id' => 6358539011, 'name' => 'Men\s Watches', 'rate' => 0.07],
        ['id' => 9818047011, 'name' => 'Echo & Alexa Devices', 'rate' => 0.07],
        ['id' => 1063306, 'name' => 'Furniture', 'rate' => 0.08],
        ['id' => 10192825011, 'name' => 'Home', 'rate' => 0.08],
        ['id' => 2972638011, 'name' => 'Lawn & Garden', 'rate' => 0.08],
        ['id' => 2619533011, 'name' => 'Pets Products', 'rate' => 0.08],
        ['id' => 7301146011, 'name' => 'Prime Pantry', 'rate' => 0.08],
        ['id' => 7141123011, 'name' => 'Amazon Fashion', 'rate' => 0.01],
        ['id' => 7175545011, 'name' => 'Luxury Beauty', 'rate' => 0.01],
    ];

    public function allOtherCategoriesRate()
    {
        return 0.04; // 4.00%
    }

    public function resolve($belongsCategories)
    {
        foreach ($belongsCategories as $belongsCategory) {
            foreach ($this->rateTable as $category) {
                if ($category['id'] == $belongsCategory['id']) {
                    return $category['rate'];
                }
            }
        }

        return $this->allOtherCategoriesRate();
    }
}