<?php

// Path: config/shophop_categories.php
//
// Master category tree for ShopHop, sourced from ITEP_308_CATEGORIES.
// Used by:
//   - buyer/dashboard/d-part-2.blade.php  (top-level "Shop by Category" tiles)
//   - buyer/category/show.blade.php       (sidebar category tree + breadcrumbs)
//
// 'folder' matches the existing images/category_icons_bg/<folder> slideshow
// directories already referenced in d-part-2.blade.php's $categorySlideshows map,
// so nothing there needs to change.

return [

    [
        'name' => 'Pet Supplies',
        'slug' => 'pet-supplies',
        'folder' => 'pet_supplies',
        'icon' => 'paw-print',
        'subcategories' => [
            'Dog Food & Treats',
            'Cat Litter & Accessories',
            'Aquariums & Fish Supplies',
            'Bird Feeders & Food',
            'Pet Grooming Products',
            'Pet Health & Wellness',
        ],
    ],

    [
        'name' => 'Electronics and Gadgets',
        'slug' => 'electronics-and-gadgets',
        'folder' => 'electronics_gadgets',
        'icon' => 'smartphone',
        'subcategories' => [
            'Mobile Phones & Accessories',
            'Laptops, Desktops & Monitors',
            'Audio & Video Equipment',
            'Smart Home Devices',
            'Cameras & Photography',
            'Wearable Technology',
        ],
    ],

    [
        'name' => "Women's Apparel",
        'slug' => 'womens-apparel',
        'folder' => 'women_apparel',
        'icon' => 'shirt',
        'subcategories' => [
            'Dresses & Skirts',
            'Tops & Blouses',
            'Activewear & Yoga Pants',
            'Lingerie & Sleepwear',
            'Jackets & Coats',
            'Shoes & Accessories',
        ],
    ],

    [
        'name' => "Men's Apparel",
        'slug' => 'mens-apparel',
        'folder' => 'men_apparel',
        'icon' => 'shirt',
        'subcategories' => [
            'Suits & Blazers',
            'Casual Shirts & Pants',
            'Outerwear & Jackets',
            'Activewear & Fitness Gear',
            'Shoes & Accessories',
            'Grooming Products',
        ],
    ],

    [
        'name' => 'Kids and Baby',
        'slug' => 'kids-and-baby',
        'folder' => 'kids_baby',
        'icon' => 'baby',
        'subcategories' => [
            'Baby Clothes & Accessories',
            'Toys & Games',
            'Educational Materials',
            'Strollers & Gear',
            'Nursery Furniture',
            'Safety and Health',
        ],
    ],

    [
        'name' => 'Home and Garden',
        'slug' => 'home-and-garden',
        'folder' => 'home_garden',
        'icon' => 'sofa',
        'subcategories' => [
            'Kitchen Appliances',
            'Furniture & Decor',
            'Gardening Tools',
            'Outdoor Living',
            'Home Improvement Tools',
            'Bedding & Bath',
        ],
    ],

    [
        'name' => 'Sports and Outdoors',
        'slug' => 'sports-and-outdoors',
        'folder' => 'sports_outdoors',
        'icon' => 'dumbbell',
        'subcategories' => [
            'Fitness Equipment',
            'Camping & Hiking Gear',
            'Sports Apparel',
            'Cycling & Bikes',
            'Water Sports',
            'Team Sports Equipment',
        ],
    ],

    [
        'name' => 'Health and Beauty',
        'slug' => 'health-and-beauty',
        'folder' => 'health_beauty',
        'icon' => 'heart-pulse',
        'subcategories' => [
            'Skincare Products',
            'Haircare Solutions',
            'Makeup & Cosmetics',
            'Personal Care Appliances',
            "Men's Grooming",
            'Health Supplements',
        ],
    ],

    [
        'name' => 'Books and Media',
        'slug' => 'books-and-media',
        'folder' => 'books_media',
        'icon' => 'book-open',
        'subcategories' => [
            'Fiction & Non-Fiction Books',
            'Magazines & Periodicals',
            'Music CDs & Vinyl Records',
            'Movie DVDs & Blu-ray',
            'Video Games & Consoles',
            'Educational DVDs',
        ],
    ],

    [
        'name' => 'Food and Gourmet',
        'slug' => 'food-and-gourmet',
        'folder' => 'food_gourmet',
        'icon' => 'utensils',
        'subcategories' => [
            'Baking Supplies & Ingredients',
            'Coffee, Tea & Beverages',
            'Snacks & Candy',
            'Specialty Foods & International Cuisine',
            'Organic and Health Foods',
            'Meal Kits & Prepped Foods',
        ],
    ],

    [
        'name' => 'Automotive & Motorcycle',
        'slug' => 'automotive-and-motorcycle',
        'folder' => 'automotive_motorcycle',
        'icon' => 'car',
        'subcategories' => [
            'Protective Gear',
            'Maintenance & Repair Tools',
            'Parts & Accessories',
            'Electrical Components',
            'Tires, Wheels, and Fluids',
        ],
    ],

    [
        'name' => 'Furniture and Office Equipment',
        'slug' => 'furniture-and-office-equipment',
        'folder' => 'furniture_office',
        'icon' => 'armchair',
        'subcategories' => [
            'Office Desks & Chairs',
            'Storage Cabinets & Shelving',
            'Conference & Meeting Furniture',
            'Computer Tables & Workstations',
            'Ergonomic Accessories',
            'Office Lighting & Fixtures',
        ],
    ],

    [
        'name' => 'Jewelry and Watches',
        'slug' => 'jewelry-and-watches',
        'folder' => 'jewelry_watches',
        'icon' => 'gem',
        'subcategories' => [
            'Necklaces & Pendants',
            'Rings & Earrings',
            'Bracelets & Bangles',
            'Watches for Men & Women',
            'Fashion Jewelry',
            'Jewelry Storage & Care',
        ],
    ],

    [
        'name' => 'Office and School Supplies',
        'slug' => 'office-and-school-supplies',
        'folder' => 'office_schoolsupplies',
        'icon' => 'pencil',
        'subcategories' => [
            'Notebooks & Paper Products',
            'Writing Instruments',
            'Office Furniture',
            'Printers & Printing Supplies',
            'School Bags & Backpacks',
            'Arts & Craft Materials',
        ],
    ],

];