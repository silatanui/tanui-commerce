<?php

return [
    'fashion' => [
        'store' => [
            'name' => 'Tanui Fashion',
            'slug' => 'fashion',
            'description' => 'A demonstration fashion house for clothing, footwear and accessories, built to show editorial merchandising and product variations.',
            'primary_color' => '#8E2F2F',
            'secondary_color' => '#161616',
            'currency' => 'KES',
        ],
        'settings' => [
            'tagline' => 'Edited pieces for the season ahead.',
            'email' => 'studio@fashion.tanui.test',
            'phone' => '+254 700 100 201',
            'address' => 'Studio 4, Riverside Drive, Nairobi',
            'hours' => 'Demonstration hours, Monday to Saturday',
            'footer_text' => 'Tanui Fashion is a fictional retailer inside the Tanui Commerce portfolio.',
            'low_stock_threshold' => '5',
        ],
        'banners' => [
            ['title' => 'The new season, edited', 'subtitle' => 'Ivory, black and sand across dresses, tailoring and shoes.', 'position' => 'hero'],
            ['title' => 'Sample prices, real interactions', 'subtitle' => 'Choose a size and colour, then walk through a simulated checkout.', 'position' => 'promo'],
        ],
        'categories' => [
            ['name' => 'Women', 'children' => ['Dresses', 'Tops']],
            ['name' => 'Men', 'children' => ['Shirts', 'Outerwear']],
            ['name' => 'Footwear'],
            ['name' => 'Accessories'],
        ],
        'brands' => [
            ['name' => 'Amani Atelier', 'description' => 'Fictional tailoring label used for dresses and outerwear.'],
            ['name' => 'Lamu Line', 'description' => 'Fictional coastal essentials for knitwear and bags.'],
            ['name' => 'Kitenge & Co', 'description' => 'Fictional shirting and jewellery house.'],
            ['name' => 'Solea', 'description' => 'Fictional footwear label.'],
        ],
        'coupon' => ['code' => 'ATELIER10', 'discount_type' => 'percentage', 'discount_value' => 10, 'minimum_amount' => 2000],
        'products' => [
            ['name' => 'Ivory Column Dress', 'category' => 'Dresses', 'brand' => 'Amani Atelier', 'price' => 8900, 'compare' => 11200, 'featured' => true, 'new' => true, 'short' => 'A narrow linen column with a clean neckline.', 'description' => 'Cut with an easy ankle length for the fashion demonstration. Size and colour options show how a variation catalogue behaves. The cloth, price and stock are fictional.', 'specs' => ['Fabric' => 'Linen blend', 'Length' => 'Ankle', 'Care' => 'Cool wash'], 'variants' => [
                ['Size' => 'S', 'Color' => 'Ivory', 'stock' => 8], ['Size' => 'M', 'Color' => 'Ivory', 'stock' => 6], ['Size' => 'L', 'Color' => 'Ivory', 'stock' => 4], ['Size' => 'M', 'Color' => 'Black', 'stock' => 5], ['Size' => 'L', 'Color' => 'Black', 'stock' => 3],
            ]],
            ['name' => 'Merino Crew Knit', 'category' => 'Tops', 'brand' => 'Lamu Line', 'price' => 5600, 'featured' => true, 'short' => 'A light crew neck for layered demonstration looks.', 'description' => 'Shown as a featured knit with colourways. Inventory on each size is sample data for the storefront filters.', 'specs' => ['Fabric' => 'Merino wool', 'Fit' => 'Regular'], 'variants' => [
                ['Size' => 'S', 'Color' => 'Sand', 'stock' => 7], ['Size' => 'M', 'Color' => 'Sand', 'stock' => 9], ['Size' => 'M', 'Color' => 'Navy', 'stock' => 4], ['Size' => 'L', 'Color' => 'Navy', 'stock' => 6],
            ]],
            ['name' => 'Cotton Poplin Shirt', 'category' => 'Shirts', 'brand' => 'Kitenge & Co', 'price' => 3900, 'new' => true, 'short' => 'A crisp poplin shirt with a soft collar.', 'description' => 'A menswear sample used to demonstrate size selection, search and category navigation.', 'specs' => ['Fabric' => 'Cotton poplin', 'Fit' => 'Tailored'], 'variants' => [
                ['Size' => 'M', 'Color' => 'Ivory', 'stock' => 10], ['Size' => 'L', 'Color' => 'Ivory', 'stock' => 8], ['Size' => 'L', 'Color' => 'Navy', 'stock' => 5], ['Size' => 'XL', 'Color' => 'Navy', 'stock' => 4],
            ]],
            ['name' => 'Wool Overcoat', 'category' => 'Outerwear', 'brand' => 'Amani Atelier', 'price' => 22000, 'featured' => true, 'short' => 'A long overcoat with a clean shoulder.', 'description' => 'The highest sample price in the fashion catalogue, useful for price filters and order totals.', 'specs' => ['Fabric' => 'Wool blend', 'Length' => 'Mid-calf'], 'variants' => [
                ['Size' => 'M', 'Color' => 'Black', 'stock' => 3], ['Size' => 'L', 'Color' => 'Black', 'stock' => 4], ['Size' => 'L', 'Color' => 'Sand', 'stock' => 2],
            ]],
            ['name' => 'Strappy Heeled Sandal', 'category' => 'Footwear', 'brand' => 'Solea', 'price' => 7800, 'compare' => 9200, 'short' => 'A leather sandal with a low heel.', 'description' => 'Footwear variations use EU sizes so the storefront can filter differently from apparel.', 'specs' => ['Material' => 'Leather', 'Heel' => '45 mm'], 'variants' => [
                ['Size' => '38', 'Color' => 'Sand', 'stock' => 4], ['Size' => '39', 'Color' => 'Sand', 'stock' => 6], ['Size' => '40', 'Color' => 'Black', 'stock' => 5],
            ]],
            ['name' => 'Everyday Sneaker', 'category' => 'Footwear', 'brand' => 'Solea', 'price' => 6900, 'featured' => true, 'short' => 'A low leather sneaker for daily demonstration looks.', 'description' => 'Paired with apparel in the related-product and cart flows.', 'specs' => ['Material' => 'Leather', 'Sole' => 'Rubber'], 'variants' => [
                ['Size' => '40', 'Color' => 'Ivory', 'stock' => 7], ['Size' => '41', 'Color' => 'Ivory', 'stock' => 5], ['Size' => '42', 'Color' => 'Black', 'stock' => 6],
            ]],
            ['name' => 'Leather Tote', 'category' => 'Accessories', 'brand' => 'Lamu Line', 'price' => 14500, 'featured' => true, 'short' => 'An unstructured tote with a magnetic close.', 'description' => 'A single-colour accessory with enough stock to test quantity updates.', 'specs' => ['Material' => 'Leather', 'Closure' => 'Magnetic'], 'variants' => [
                ['Color' => 'Sand', 'stock' => 6], ['Color' => 'Black', 'stock' => 4],
            ]],
            ['name' => 'Beaded Collar Necklace', 'category' => 'Accessories', 'brand' => 'Kitenge & Co', 'price' => 2400, 'new' => true, 'short' => 'A short collar necklace in glass beads.', 'description' => 'A lower sample price so the market and fashion catalogues feel different in the cart.', 'specs' => ['Material' => 'Glass bead', 'Length' => '40 cm'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 12], ['Color' => 'Navy', 'stock' => 9],
            ]],
        ],
    ],
    'electronics' => [
        'store' => [
            'name' => 'Tanui Electronics',
            'slug' => 'electronics',
            'description' => 'A demonstration electronics shop with specifications, warranty notes, comparisons and clear sample pricing.',
            'primary_color' => '#087EA4',
            'secondary_color' => '#101820',
            'currency' => 'KES',
        ],
        'settings' => [
            'tagline' => 'Specifications, compared and ready to explore.',
            'email' => 'desk@electronics.tanui.test',
            'phone' => '+254 700 100 202',
            'address' => '14 Waiyaki Way, Westlands, Nairobi',
            'hours' => 'Demonstration desk, every day',
            'footer_text' => 'Tanui Electronics does not sell devices. Warranty text is fictional.',
            'low_stock_threshold' => '4',
        ],
        'banners' => [
            ['title' => 'Specifications first', 'subtitle' => 'Phones, computers and audio with sample stock and comparison.', 'position' => 'hero'],
            ['title' => 'Compare three products', 'subtitle' => 'Use the comparison tray to line up fictional specifications.', 'position' => 'promo'],
        ],
        'categories' => [
            ['name' => 'Phones'],
            ['name' => 'Computers'],
            ['name' => 'Audio'],
            ['name' => 'Home Electronics'],
            ['name' => 'Accessories'],
        ],
        'brands' => [
            ['name' => 'Nimbus', 'description' => 'Fictional phone and watch brand.'],
            ['name' => 'Fieldnote', 'description' => 'Fictional computer and camera brand.'],
            ['name' => 'Aria Audio', 'description' => 'Fictional audio brand.'],
            ['name' => 'Volt & Co', 'description' => 'Fictional home electronics brand.'],
        ],
        'coupon' => ['code' => 'VOLT5', 'discount_type' => 'percentage', 'discount_value' => 5, 'minimum_amount' => 5000],
        'products' => [
            ['name' => 'Nimbus Phone X', 'category' => 'Phones', 'brand' => 'Nimbus', 'price' => 64999, 'featured' => true, 'short' => 'A fictional flagship with two storage options.', 'description' => 'Built for the electronics demonstration: storage variants, a specification table and a sample warranty. No network service is included.', 'specs' => ['Display' => '6.5 inch OLED', 'Warranty' => '12-month demonstration warranty', 'Chip' => 'Nimbus N3'], 'variants' => [
                ['Storage' => '128GB', 'Color' => 'Black', 'stock' => 8, 'price' => 64999], ['Storage' => '256GB', 'Color' => 'Black', 'stock' => 5, 'price' => 72999], ['Storage' => '256GB', 'Color' => 'Silver', 'stock' => 4, 'price' => 72999],
            ]],
            ['name' => 'Fieldnote Laptop 14', 'category' => 'Computers', 'brand' => 'Fieldnote', 'price' => 112000, 'featured' => true, 'short' => 'A 14-inch laptop sample for work and study displays.', 'description' => 'The product page emphasises memory, display and warranty. Prices are samples and nothing is shipped.', 'specs' => ['Display' => '14 inch 2.2K', 'Memory' => '16 GB', 'Warranty' => '24-month demonstration warranty'], 'variants' => [
                ['Storage' => '512GB', 'Color' => 'Silver', 'stock' => 4], ['Storage' => '1TB', 'Color' => 'Silver', 'stock' => 3, 'price' => 128000],
            ]],
            ['name' => 'Aria Buds Pro', 'category' => 'Audio', 'brand' => 'Aria Audio', 'price' => 8900, 'compare' => 10900, 'new' => true, 'short' => 'In-ear buds with a charging case.', 'description' => 'A lower-priced audio sample with a compare-at price so sale badges and discounts can be shown.', 'specs' => ['Battery' => '24 hours with case', 'Warranty' => '6-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 15], ['Color' => 'Ivory', 'stock' => 9],
            ]],
            ['name' => 'Studio Headphones', 'category' => 'Audio', 'brand' => 'Aria Audio', 'price' => 12400, 'featured' => true, 'short' => 'Over-ear headphones with noise reduction.', 'description' => 'Useful in the comparison tray beside the earbuds and soundbar.', 'specs' => ['Driver' => '40 mm', 'Battery' => '30 hours', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 7], ['Color' => 'Silver', 'stock' => 5],
            ]],
            ['name' => 'Volt Soundbar', 'category' => 'Home Electronics', 'brand' => 'Volt & Co', 'price' => 15600, 'short' => 'A compact soundbar for the living-room demonstration.', 'description' => 'Shows home electronics next to personal audio, with availability driven by variant stock.', 'specs' => ['Channels' => '2.1', 'Connections' => 'HDMI and optical', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 6],
            ]],
            ['name' => 'Mirrorless Camera Kit', 'category' => 'Home Electronics', 'brand' => 'Fieldnote', 'price' => 96500, 'featured' => true, 'short' => 'A body and lens kit with sample specifications.', 'description' => 'A high sample price for analytics and average-order demonstrations.', 'specs' => ['Sensor' => '26 MP', 'Lens' => '24-70 mm sample', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 3],
            ]],
            ['name' => 'Mechanical Keyboard', 'category' => 'Accessories', 'brand' => 'Volt & Co', 'price' => 7400, 'new' => true, 'short' => 'A compact keyboard with a quiet switch.', 'description' => 'An accessory that pairs with the laptop in related products and the cart.', 'specs' => ['Layout' => '75 percent', 'Connection' => 'USB-C'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 8], ['Color' => 'Black', 'stock' => 6],
            ]],
            ['name' => 'Smart Watch S', 'category' => 'Accessories', 'brand' => 'Nimbus', 'price' => 18999, 'short' => 'A watch sample with health-style demonstration screens.', 'description' => 'Included so brand filters can group Nimbus products that are not phones.', 'specs' => ['Display' => '1.7 inch', 'Battery' => '2 days', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 7], ['Color' => 'Silver', 'stock' => 4],
            ]],
        ],
    ],
    'home' => [
        'store' => [
            'name' => 'Tanui Home',
            'slug' => 'home',
            'description' => 'A demonstration furniture and home-accessories studio with dimensions, materials and large product imagery.',
            'primary_color' => '#8D6A45',
            'secondary_color' => '#2C2825',
            'currency' => 'KES',
        ],
        'settings' => [
            'tagline' => 'Furniture and objects for quieter rooms.',
            'email' => 'studio@home.tanui.test',
            'phone' => '+254 700 100 203',
            'address' => '8 Argwings Kodhek Road, Kilimani, Nairobi',
            'hours' => 'Showroom demonstration, Thursday to Sunday',
            'footer_text' => 'Delivery notes are simulated. No furniture leaves the demonstration.',
            'low_stock_threshold' => '3',
        ],
        'banners' => [
            ['title' => 'Rooms, composed', 'subtitle' => 'Seating, tables and objects with materials and dimensions.', 'position' => 'hero'],
            ['title' => 'Delivery is explained, not fulfilled', 'subtitle' => 'Checkout shows collection and delivery options without dispatching anything.', 'position' => 'promo'],
        ],
        'categories' => [
            ['name' => 'Living Room'],
            ['name' => 'Bedroom'],
            ['name' => 'Office'],
            ['name' => 'Dining'],
            ['name' => 'Accessories'],
        ],
        'brands' => [
            ['name' => 'Habita', 'description' => 'Fictional upholstery studio.'],
            ['name' => 'Oak & Linen', 'description' => 'Fictional timber furniture label.'],
            ['name' => 'Studio North', 'description' => 'Fictional desk and chair studio.'],
            ['name' => 'Kiln', 'description' => 'Fictional ceramics and textiles.'],
        ],
        'coupon' => ['code' => 'HABITA', 'discount_type' => 'fixed', 'discount_value' => 1500, 'minimum_amount' => 10000],
        'products' => [
            ['name' => 'Three-Seat Linen Sofa', 'category' => 'Living Room', 'brand' => 'Habita', 'price' => 89000, 'featured' => true, 'short' => 'A low three-seat sofa in oatmeal linen.', 'description' => 'The hero furniture piece. Material variations and dimensions are shown on the product page. Delivery text is a demonstration only.', 'specs' => ['Width' => '220 cm', 'Depth' => '92 cm', 'Material' => 'Linen and hardwood', 'Delivery' => 'Simulated room-of-choice note'], 'variants' => [
                ['Material' => 'Linen', 'Color' => 'Sand', 'stock' => 3], ['Material' => 'Linen', 'Color' => 'Ivory', 'stock' => 2],
            ]],
            ['name' => 'Lounge Chair', 'category' => 'Living Room', 'brand' => 'Studio North', 'price' => 27500, 'featured' => true, 'short' => 'A timber lounge chair with a loose cushion.', 'description' => 'A smaller seating sample so the living-room category has more than one product.', 'specs' => ['Width' => '72 cm', 'Material' => 'Oak and linen'], 'variants' => [
                ['Material' => 'Oak', 'Color' => 'Sand', 'stock' => 5],
            ]],
            ['name' => 'Oak Dining Table', 'category' => 'Dining', 'brand' => 'Oak & Linen', 'price' => 64000, 'featured' => true, 'short' => 'A six-seat table in white oak.', 'description' => 'Shown with the dining chair as a related collection. Dimensions support the furniture brief.', 'specs' => ['Length' => '180 cm', 'Seats' => '6', 'Material' => 'White oak'], 'variants' => [
                ['Material' => 'Oak', 'Color' => 'Sand', 'stock' => 2],
            ]],
            ['name' => 'Dining Chair', 'category' => 'Dining', 'brand' => 'Oak & Linen', 'price' => 7500, 'short' => 'A spindle-back chair sold as a single sample.', 'description' => 'Quantity selection lets a visitor add several chairs without implying a real delivery.', 'specs' => ['Height' => '80 cm', 'Material' => 'Oak'], 'variants' => [
                ['Material' => 'Oak', 'Color' => 'Sand', 'stock' => 12],
            ]],
            ['name' => 'Walnut Desk', 'category' => 'Office', 'brand' => 'Studio North', 'price' => 38500, 'new' => true, 'short' => 'A writing desk with a single drawer.', 'description' => 'Office furniture for the home demonstration, with a clear width and material.', 'specs' => ['Width' => '140 cm', 'Material' => 'Walnut'], 'variants' => [
                ['Material' => 'Walnut', 'Color' => 'Brown', 'stock' => 4],
            ]],
            ['name' => 'Task Chair', 'category' => 'Office', 'brand' => 'Habita', 'price' => 21400, 'short' => 'An upholstered task chair with a swivel base.', 'description' => 'Pairs with the desk in the office category and in comparison.', 'specs' => ['Base' => 'Five-star', 'Material' => 'Wool blend'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 6], ['Color' => 'Black', 'stock' => 3],
            ]],
            ['name' => 'Linen Duvet Set', 'category' => 'Bedroom', 'brand' => 'Kiln', 'price' => 8900, 'new' => true, 'short' => 'A washed linen duvet cover and two pillowcases.', 'description' => 'A textile sample with size variations, closer to apparel than furniture.', 'specs' => ['Fabric' => 'Washed linen', 'Set' => 'Duvet and two pillows'], 'variants' => [
                ['Size' => 'Double', 'Color' => 'Ivory', 'stock' => 8], ['Size' => 'Queen', 'Color' => 'Sand', 'stock' => 5],
            ]],
            ['name' => 'Ceramic Table Lamp', 'category' => 'Accessories', 'brand' => 'Kiln', 'price' => 6200, 'compare' => 7400, 'short' => 'A glazed ceramic lamp with a linen shade.', 'description' => 'A home accessory with a compare-at sample price.', 'specs' => ['Height' => '48 cm', 'Material' => 'Ceramic'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 9], ['Color' => 'Sand', 'stock' => 4],
            ]],
        ],
    ],
    'market' => [
        'store' => [
            'name' => 'Tanui Market',
            'slug' => 'market',
            'description' => 'A demonstration grocery and household shop with units, quantities and straightforward category navigation.',
            'primary_color' => '#2F7D46',
            'secondary_color' => '#1C2B22',
            'currency' => 'KES',
        ],
        'settings' => [
            'tagline' => 'A clearer way to do the weekly shop.',
            'email' => 'hello@market.tanui.test',
            'phone' => '+254 700 100 204',
            'address' => '210 Ngong Road, Nairobi',
            'hours' => 'Demonstration aisle, 8:00 to 20:00',
            'footer_text' => 'Food and household goods are fictional. Nothing is packed or delivered.',
            'low_stock_threshold' => '6',
        ],
        'banners' => [
            ['title' => 'The weekly shop, simplified', 'subtitle' => 'Pantry, drinks and household goods with units and sample prices.', 'position' => 'hero'],
            ['title' => 'Collection or simulated delivery', 'subtitle' => 'Choose a delivery option at checkout. No order is fulfilled.', 'position' => 'promo'],
        ],
        'categories' => [
            ['name' => 'Fresh Food'],
            ['name' => 'Pantry'],
            ['name' => 'Beverages'],
            ['name' => 'Household'],
            ['name' => 'Personal Care'],
        ],
        'brands' => [
            ['name' => 'Harvest Row', 'description' => 'Fictional fresh and grocery label.'],
            ['name' => 'Springwell', 'description' => 'Fictional drinks label.'],
            ['name' => 'Daily Goods', 'description' => 'Fictional staple goods label.'],
            ['name' => 'Homestead', 'description' => 'Fictional household label.'],
        ],
        'coupon' => ['code' => 'BASKET10', 'discount_type' => 'percentage', 'discount_value' => 10, 'minimum_amount' => 500],
        'products' => [
            ['name' => 'Sourdough Loaf', 'category' => 'Fresh Food', 'brand' => 'Harvest Row', 'price' => 280, 'new' => true, 'short' => 'A sample loaf sold by the piece.', 'description' => 'A fresh-food sample with a simple unit so quantity selection is obvious.', 'specs' => ['Unit' => '1 loaf', 'Storage' => 'Sample bakery note'], 'variants' => [
                ['Unit' => '1 loaf', 'stock' => 20],
            ]],
            ['name' => 'Single-origin Coffee', 'category' => 'Pantry', 'brand' => 'Springwell', 'price' => 890, 'featured' => true, 'short' => 'Beans from a fictional lot, sold in two sizes.', 'description' => 'Unit variations show how a grocery catalogue handles weight without real sourcing.', 'specs' => ['Origin' => 'Fictional lot', 'Roast' => 'Medium'], 'variants' => [
                ['Unit' => '250 g', 'stock' => 18], ['Unit' => '500 g', 'stock' => 11, 'price' => 1650],
            ]],
            ['name' => 'Olive Oil', 'category' => 'Pantry', 'brand' => 'Homestead', 'price' => 1450, 'featured' => true, 'short' => 'A 500 ml bottle of sample olive oil.', 'description' => 'A pantry staple used in search, brand filters and order history.', 'specs' => ['Volume' => '500 ml'], 'variants' => [
                ['Unit' => '500 ml', 'stock' => 14],
            ]],
            ['name' => 'Basmati Rice', 'category' => 'Pantry', 'brand' => 'Daily Goods', 'price' => 620, 'short' => 'A 2 kg bag of sample basmati rice.', 'description' => 'Included so household and pantry totals behave like a real basket.', 'specs' => ['Weight' => '2 kg'], 'variants' => [
                ['Unit' => '2 kg', 'stock' => 16],
            ]],
            ['name' => 'Cold Pressed Juice', 'category' => 'Beverages', 'brand' => 'Springwell', 'price' => 350, 'new' => true, 'short' => 'A 750 ml bottle of mixed fruit juice.', 'description' => 'A beverage with a short sample shelf-life note. Nothing is chilled or dispatched.', 'specs' => ['Volume' => '750 ml'], 'variants' => [
                ['Unit' => '750 ml', 'stock' => 22],
            ]],
            ['name' => 'Sparkling Water', 'category' => 'Beverages', 'brand' => 'Daily Goods', 'price' => 540, 'short' => 'A six-bottle pack of sparkling water.', 'description' => 'Pack quantity is the variant unit, while the cart quantity multiplies packs.', 'specs' => ['Pack' => '6 x 500 ml'], 'variants' => [
                ['Unit' => '6 pack', 'stock' => 15],
            ]],
            ['name' => 'Cotton Towel Set', 'category' => 'Household', 'brand' => 'Homestead', 'price' => 1650, 'featured' => true, 'compare' => 1900, 'short' => 'Two bath towels and two hand towels.', 'description' => 'A household textile with colour variations and a compare-at sample price.', 'specs' => ['Set' => '4 pieces', 'Material' => 'Cotton'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 8], ['Color' => 'Sand', 'stock' => 6],
            ]],
            ['name' => 'Hand Cream', 'category' => 'Personal Care', 'brand' => 'Harvest Row', 'price' => 480, 'short' => 'A 75 ml tube of unscented hand cream.', 'description' => 'A personal-care sample so the market catalogue covers more than food.', 'specs' => ['Volume' => '75 ml'], 'variants' => [
                ['Unit' => '75 ml', 'stock' => 19],
            ]],
        ],
    ],
];
