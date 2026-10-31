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
            ['name' => 'Ivory Column Dress', 'category' => 'Dresses', 'brand' => 'Amani Atelier', 'price' => 8900, 'compare' => 11200, 'featured' => true, 'new' => true, 'short' => 'A refined ankle-length column in breathable linen with a clean neckline and an elegant, easy drape.', 'description' => 'Designed for warm evenings and polished daytime events, this dress skims the body without clinging. The linen blend softens with wear while holding its shape through busy days. Cool wash and line dry help preserve colour and texture. Choose your size and shade to complete the look.', 'specs' => ['Fabric' => 'Linen blend', 'Length' => 'Ankle', 'Care' => 'Cool wash'], 'variants' => [
                ['Size' => 'S', 'Color' => 'Ivory', 'stock' => 8], ['Size' => 'M', 'Color' => 'Ivory', 'stock' => 6], ['Size' => 'L', 'Color' => 'Ivory', 'stock' => 4], ['Size' => 'M', 'Color' => 'Black', 'stock' => 5], ['Size' => 'L', 'Color' => 'Black', 'stock' => 3],
            ]],
            ['name' => 'Merino Crew Knit', 'category' => 'Tops', 'brand' => 'Lamu Line', 'price' => 5600, 'featured' => true, 'short' => 'Lightweight merino in a classic crew neck, perfect for layering or wearing on its own.', 'description' => 'Spun from soft merino wool with a regular fit that sits neatly at the hip. Sand and navy pair easily with denim, skirts, or tailored trousers. Hand wash cold or dry clean to keep fibres smooth and resilient. A year-round staple from Lamu Line.', 'specs' => ['Fabric' => 'Merino wool', 'Fit' => 'Regular'], 'variants' => [
                ['Size' => 'S', 'Color' => 'Sand', 'stock' => 7], ['Size' => 'M', 'Color' => 'Sand', 'stock' => 9], ['Size' => 'M', 'Color' => 'Navy', 'stock' => 4], ['Size' => 'L', 'Color' => 'Navy', 'stock' => 6],
            ]],
            ['name' => 'Cotton Poplin Shirt', 'category' => 'Shirts', 'brand' => 'Kitenge & Co', 'price' => 3900, 'new' => true, 'short' => 'Crisp cotton poplin with a softened collar and tailored fit for office days or weekend plans.', 'description' => 'Breathable poplin keeps you comfortable from morning meetings to evening out. The tailored cut defines the shoulder without feeling restrictive. Machine wash warm and tumble dry low, or press for a sharper finish. Available in ivory and navy from Kitenge & Co.', 'specs' => ['Fabric' => 'Cotton poplin', 'Fit' => 'Tailored'], 'variants' => [
                ['Size' => 'M', 'Color' => 'Ivory', 'stock' => 10], ['Size' => 'L', 'Color' => 'Ivory', 'stock' => 8], ['Size' => 'L', 'Color' => 'Navy', 'stock' => 5], ['Size' => 'XL', 'Color' => 'Navy', 'stock' => 4],
            ]],
            ['name' => 'Wool Overcoat', 'category' => 'Outerwear', 'brand' => 'Amani Atelier', 'price' => 22000, 'featured' => true, 'short' => 'A mid-calf wool-blend overcoat with a structured shoulder and timeless silhouette for cold seasons.', 'description' => 'Layer over tailoring or knitwear for commutes, travel, and formal occasions. The wool blend offers warmth without excessive bulk. Dry clean only to maintain the coat\'s drape and colour depth. An investment piece from Amani Atelier.', 'specs' => ['Fabric' => 'Wool blend', 'Length' => 'Mid-calf'], 'variants' => [
                ['Size' => 'M', 'Color' => 'Black', 'stock' => 3], ['Size' => 'L', 'Color' => 'Black', 'stock' => 4], ['Size' => 'L', 'Color' => 'Sand', 'stock' => 2],
            ]],
            ['name' => 'Strappy Heeled Sandal', 'category' => 'Footwear', 'brand' => 'Solea', 'price' => 7800, 'compare' => 9200, 'short' => 'Leather strappy sandal with a comfortable 45 mm heel, made for evenings and dressed-up days.', 'description' => 'Supple leather straps secure the foot while the low heel adds lift without sacrificing stability. Sand and black finish options suit summer dresses and tailored separates. Wipe clean and store away from direct heat to protect the leather. Sized in EU from Solea.', 'specs' => ['Material' => 'Leather', 'Heel' => '45 mm'], 'variants' => [
                ['Size' => '38', 'Color' => 'Sand', 'stock' => 4], ['Size' => '39', 'Color' => 'Sand', 'stock' => 6], ['Size' => '40', 'Color' => 'Black', 'stock' => 5],
            ]],
            ['name' => 'Everyday Sneaker', 'category' => 'Footwear', 'brand' => 'Solea', 'price' => 6900, 'featured' => true, 'short' => 'Low-profile leather sneaker with a rubber sole, built for everyday comfort and clean styling.', 'description' => 'A minimal silhouette pairs with denim, chinos, or relaxed dresses. The leather upper breaks in gently while the rubber sole grips city pavements. Spot clean leather and air dry away from radiators. Your go-to daily shoe from Solea.', 'specs' => ['Material' => 'Leather', 'Sole' => 'Rubber'], 'variants' => [
                ['Size' => '40', 'Color' => 'Ivory', 'stock' => 7], ['Size' => '41', 'Color' => 'Ivory', 'stock' => 5], ['Size' => '42', 'Color' => 'Black', 'stock' => 6],
            ]],
            ['name' => 'Leather Tote', 'category' => 'Accessories', 'brand' => 'Lamu Line', 'price' => 14500, 'featured' => true, 'short' => 'Unstructured leather tote with a magnetic closure, roomy enough for work essentials and weekend errands.', 'description' => 'Soft leather moulds to your load while the magnetic flap keeps contents secure on the move. Fits a laptop sleeve, planner, and daily basics without feeling stiff. Condition leather periodically to maintain suppleness and colour. From Lamu Line in sand or black.', 'specs' => ['Material' => 'Leather', 'Closure' => 'Magnetic'], 'variants' => [
                ['Color' => 'Sand', 'stock' => 6], ['Color' => 'Black', 'stock' => 4],
            ]],
            ['name' => 'Beaded Collar Necklace', 'category' => 'Accessories', 'brand' => 'Kitenge & Co', 'price' => 2400, 'new' => true, 'short' => 'Short collar necklace in hand-finished glass beads, ideal for elevating necklines and layered looks.', 'description' => 'Sits at the collarbone to frame boat necks, shirts, and evening dresses. Lightweight beads add colour without pulling on the neck. Store flat and avoid moisture to protect the finish. A finishing touch from Kitenge & Co in ivory or navy.', 'specs' => ['Material' => 'Glass bead', 'Length' => '40 cm'], 'variants' => [
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
            ['name' => 'Nimbus Phone X', 'category' => 'Phones', 'brand' => 'Nimbus', 'price' => 64999, 'featured' => true, 'short' => 'Flagship smartphone with a vivid 6.5 inch OLED display and powerful Nimbus N3 chip inside.', 'description' => 'Sharp photos, smooth scrolling, and all-day performance for work and entertainment on the go. Choose 128GB or 256GB storage in black or silver to match how you use your phone. Includes a 12-month manufacturer warranty for peace of mind. Network plans sold separately.', 'specs' => ['Display' => '6.5 inch OLED', 'Warranty' => '12-month demonstration warranty', 'Chip' => 'Nimbus N3'], 'variants' => [
                ['Storage' => '128GB', 'Color' => 'Black', 'stock' => 8, 'price' => 64999], ['Storage' => '256GB', 'Color' => 'Black', 'stock' => 5, 'price' => 72999], ['Storage' => '256GB', 'Color' => 'Silver', 'stock' => 4, 'price' => 72999],
            ]],
            ['name' => 'Fieldnote Laptop 14', 'category' => 'Computers', 'brand' => 'Fieldnote', 'price' => 112000, 'featured' => true, 'short' => '14-inch laptop with a crisp 2.2K display and 16 GB memory for productivity on the move.', 'description' => 'Built for students, remote workers, and creators who need a balanced screen and reliable performance. 512GB or 1TB storage options hold projects, media, and apps without constant cloud juggling. Backed by a 24-month warranty for hardware defects. Lightweight enough for daily carry in a backpack.', 'specs' => ['Display' => '14 inch 2.2K', 'Memory' => '16 GB', 'Warranty' => '24-month demonstration warranty'], 'variants' => [
                ['Storage' => '512GB', 'Color' => 'Silver', 'stock' => 4], ['Storage' => '1TB', 'Color' => 'Silver', 'stock' => 3, 'price' => 128000],
            ]],
            ['name' => 'Aria Buds Pro', 'category' => 'Audio', 'brand' => 'Aria Audio', 'price' => 8900, 'compare' => 10900, 'new' => true, 'short' => 'True wireless in-ear buds with a charging case delivering up to 24 hours of listening.', 'description' => 'Secure fit and balanced sound for commutes, workouts, and calls without bulky over-ear gear. The case tops up battery between sessions so you rarely reach for a cable. Six-month warranty covers manufacturing faults. Available in black or ivory from Aria Audio.', 'specs' => ['Battery' => '24 hours with case', 'Warranty' => '6-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 15], ['Color' => 'Ivory', 'stock' => 9],
            ]],
            ['name' => 'Studio Headphones', 'category' => 'Audio', 'brand' => 'Aria Audio', 'price' => 12400, 'featured' => true, 'short' => 'Over-ear headphones with active noise reduction and 30-hour battery for long listening sessions.', 'description' => '40 mm drivers deliver full bass and clear vocals for music, podcasts, and focused work. Fold-friendly design and cushioned ear cups reduce fatigue on flights and desk days. Twelve-month warranty included. Choose black or silver to match your setup.', 'specs' => ['Driver' => '40 mm', 'Battery' => '30 hours', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 7], ['Color' => 'Silver', 'stock' => 5],
            ]],
            ['name' => 'Volt Soundbar', 'category' => 'Home Electronics', 'brand' => 'Volt & Co', 'price' => 15600, 'short' => 'Compact 2.1 soundbar that upgrades TV dialogue and music with HDMI and optical connections.', 'description' => 'Fits under most screens without dominating the room while adding noticeable depth to movies and playlists. Simple hookup to TVs, streamers, and consoles keeps cable clutter low. Twelve-month warranty on the unit. A living-room upgrade from Volt & Co.', 'specs' => ['Channels' => '2.1', 'Connections' => 'HDMI and optical', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 6],
            ]],
            ['name' => 'Mirrorless Camera Kit', 'category' => 'Home Electronics', 'brand' => 'Fieldnote', 'price' => 96500, 'featured' => true, 'short' => '26 MP mirrorless camera body with a versatile 24-70 mm lens kit for photo and video.', 'description' => 'Responsive autofocus and strong low-light performance suit travel, portraits, and content creation. The included zoom lens covers wide scenes and tighter framing without swapping glass constantly. Twelve-month warranty on body and lens. Ready to shoot out of the box from Fieldnote.', 'specs' => ['Sensor' => '26 MP', 'Lens' => '24-70 mm sample', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
                ['Color' => 'Black', 'stock' => 3],
            ]],
            ['name' => 'Mechanical Keyboard', 'category' => 'Accessories', 'brand' => 'Volt & Co', 'price' => 7400, 'new' => true, 'short' => '75 percent mechanical keyboard with quiet switches and USB-C connection for tidy desks.', 'description' => 'More desk space than a full layout while keeping arrows and function keys within reach. Tactile yet office-friendly keystrokes suit typing marathons and light gaming. Plug-and-play over USB-C with ivory or black keycaps. Pairs well with laptops and compact workstations.', 'specs' => ['Layout' => '75 percent', 'Connection' => 'USB-C'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 8], ['Color' => 'Black', 'stock' => 6],
            ]],
            ['name' => 'Smart Watch S', 'category' => 'Accessories', 'brand' => 'Nimbus', 'price' => 18999, 'short' => 'Smart watch with a bright 1.7 inch display and roughly two days of battery per charge.', 'description' => 'Track steps, heart rate, and notifications from your wrist without reaching for your phone. Lightweight case and interchangeable bands suit gym sessions and office wear. Twelve-month warranty covers hardware issues. Complements the Nimbus phone ecosystem in black or silver.', 'specs' => ['Display' => '1.7 inch', 'Battery' => '2 days', 'Warranty' => '12-month demonstration warranty'], 'variants' => [
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
            ['name' => 'Three-Seat Linen Sofa', 'category' => 'Living Room', 'brand' => 'Habita', 'price' => 89000, 'featured' => true, 'short' => 'Low-profile three-seat sofa upholstered in oatmeal linen with a hardwood frame for relaxed living rooms.', 'description' => 'Generous seating depth and soft linen invite long evenings with family or guests. Hardwood legs and internal framing support daily use without sagging. Choose sand or ivory linen to match your palette. Delivery notes at checkout explain room-of-choice placement options.', 'specs' => ['Width' => '220 cm', 'Depth' => '92 cm', 'Material' => 'Linen and hardwood', 'Delivery' => 'Simulated room-of-choice note'], 'variants' => [
                ['Material' => 'Linen', 'Color' => 'Sand', 'stock' => 3], ['Material' => 'Linen', 'Color' => 'Ivory', 'stock' => 2],
            ]],
            ['name' => 'Lounge Chair', 'category' => 'Living Room', 'brand' => 'Studio North', 'price' => 27500, 'featured' => true, 'short' => 'Oak lounge chair with a loose linen cushion, sized for reading nooks and compact living spaces.', 'description' => 'Solid oak frame and a removable cushion make this an easy accent beside sofas or windows. The sand upholstery stays neutral with most colour schemes. Wipe timber with a dry cloth and vacuum the cushion cover as needed. From Studio North.', 'specs' => ['Width' => '72 cm', 'Material' => 'Oak and linen'], 'variants' => [
                ['Material' => 'Oak', 'Color' => 'Sand', 'stock' => 5],
            ]],
            ['name' => 'Oak Dining Table', 'category' => 'Dining', 'brand' => 'Oak & Linen', 'price' => 64000, 'featured' => true, 'short' => 'Six-seat dining table in white oak, 180 cm long, built for family meals and entertaining.', 'description' => 'Clean lines and a natural sand finish highlight the grain without overpowering your chairs. Seats six comfortably with room for serving dishes in the centre. Protect with coasters and occasional oil suited to oak. Pair with matching Oak & Linen dining chairs.', 'specs' => ['Length' => '180 cm', 'Seats' => '6', 'Material' => 'White oak'], 'variants' => [
                ['Material' => 'Oak', 'Color' => 'Sand', 'stock' => 2],
            ]],
            ['name' => 'Dining Chair', 'category' => 'Dining', 'brand' => 'Oak & Linen', 'price' => 7500, 'short' => 'Spindle-back dining chair in white oak, sold individually so you can seat the table your way.', 'description' => 'Classic back details and a sturdy oak build stand up to daily meals. Stack-friendly enough for occasional extra guests when you add more chairs over time. Dust regularly and treat wood as you would other oak pieces. Matches the Oak & Linen dining table.', 'specs' => ['Height' => '80 cm', 'Material' => 'Oak'], 'variants' => [
                ['Material' => 'Oak', 'Color' => 'Sand', 'stock' => 12],
            ]],
            ['name' => 'Walnut Desk', 'category' => 'Office', 'brand' => 'Studio North', 'price' => 38500, 'new' => true, 'short' => '140 cm walnut writing desk with one drawer, ideal for home offices and study corners.', 'description' => 'Smooth walnut surface fits a laptop, lamp, and notebooks without crowding the room. The single drawer hides cables and stationery for a cleaner workspace. Oil walnut occasionally to maintain tone and resist marks. A focused workstation from Studio North.', 'specs' => ['Width' => '140 cm', 'Material' => 'Walnut'], 'variants' => [
                ['Material' => 'Walnut', 'Color' => 'Brown', 'stock' => 4],
            ]],
            ['name' => 'Task Chair', 'category' => 'Office', 'brand' => 'Habita', 'price' => 21400, 'short' => 'Upholstered task chair on a five-star swivel base, supportive for long hours at the desk.', 'description' => 'Wool-blend padding and adjustable height help you sit comfortably through calls and deep work. Ivory and black upholstery options suit light or dark office schemes. Spot clean fabric and check casters on hard floors periodically. Designed to pair with home office desks.', 'specs' => ['Base' => 'Five-star', 'Material' => 'Wool blend'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 6], ['Color' => 'Black', 'stock' => 3],
            ]],
            ['name' => 'Linen Duvet Set', 'category' => 'Bedroom', 'brand' => 'Kiln', 'price' => 8900, 'new' => true, 'short' => 'Washed linen duvet cover set with two pillowcases, breathable for warm nights and easy layering.', 'description' => 'Pre-washed linen softens further with every wash while staying crisp at the hem. Double and queen sizes cover most bedroom setups in ivory or sand. Machine wash gentle and line dry for best longevity. From Kiln for a relaxed bedroom look.', 'specs' => ['Fabric' => 'Washed linen', 'Set' => 'Duvet and two pillows'], 'variants' => [
                ['Size' => 'Double', 'Color' => 'Ivory', 'stock' => 8], ['Size' => 'Queen', 'Color' => 'Sand', 'stock' => 5],
            ]],
            ['name' => 'Ceramic Table Lamp', 'category' => 'Accessories', 'brand' => 'Kiln', 'price' => 6200, 'compare' => 7400, 'short' => 'Glazed ceramic table lamp with a linen shade, 48 cm tall, for bedside and side tables.', 'description' => 'Warm diffused light suits reading corners and living-room accents without harsh glare. Ivory or sand glaze complements neutral interiors. Use a bulb rated for the socket and dust the shade gently. A finishing touch from Kiln at a promotional price while stock lasts.', 'specs' => ['Height' => '48 cm', 'Material' => 'Ceramic'], 'variants' => [
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
            ['name' => 'Sourdough Loaf', 'category' => 'Fresh Food', 'brand' => 'Harvest Row', 'price' => 280, 'new' => true, 'short' => 'Artisan sourdough loaf with a crisp crust and open crumb, baked fresh and sold by the loaf.', 'description' => 'Best enjoyed within a day or two, toasted for breakfast or served with soup and salads. Store in a paper bag at room temperature to keep the crust firm. Each loaf is one unit for easy basket quantities. From Harvest Row bakery line.', 'specs' => ['Unit' => '1 loaf', 'Storage' => 'Sample bakery note'], 'variants' => [
                ['Unit' => '1 loaf', 'stock' => 20],
            ]],
            ['name' => 'Single-origin Coffee', 'category' => 'Pantry', 'brand' => 'Springwell', 'price' => 890, 'featured' => true, 'short' => 'Single-origin medium roast coffee beans, available in 250 g or 500 g bags for home brewing.', 'description' => 'Roasted for balanced chocolate and citrus notes in pour-over, French press, or drip machines. Grind fresh at home for the fullest aroma. Reseal the bag and store in a cool, dark cupboard. A pantry favourite from Springwell.', 'specs' => ['Origin' => 'Fictional lot', 'Roast' => 'Medium'], 'variants' => [
                ['Unit' => '250 g', 'stock' => 18], ['Unit' => '500 g', 'stock' => 11, 'price' => 1650],
            ]],
            ['name' => 'Olive Oil', 'category' => 'Pantry', 'brand' => 'Homestead', 'price' => 1450, 'featured' => true, 'short' => '500 ml bottle of extra-virgin olive oil for dressings, roasting, and everyday Mediterranean cooking.', 'description' => 'Cold-pressed flavour suits salads, pasta, and finishing grilled vegetables. Keep the bottle capped and away from heat to preserve taste. One bottle per unit makes pantry restocking straightforward. A Homestead staple for weekly shops.', 'specs' => ['Volume' => '500 ml'], 'variants' => [
                ['Unit' => '500 ml', 'stock' => 14],
            ]],
            ['name' => 'Basmati Rice', 'category' => 'Pantry', 'brand' => 'Daily Goods', 'price' => 620, 'short' => '2 kg bag of long-grain basmati rice with a light aroma, ideal for curries and pilafs.', 'description' => 'Rinses to fluffy separate grains that pair with stews, grilled meats, and vegetarian mains. Store sealed in a dry pantry to prevent moisture. Family-sized bag covers many meals from Daily Goods.', 'specs' => ['Weight' => '2 kg'], 'variants' => [
                ['Unit' => '2 kg', 'stock' => 16],
            ]],
            ['name' => 'Cold Pressed Juice', 'category' => 'Beverages', 'brand' => 'Springwell', 'price' => 350, 'new' => true, 'short' => '750 ml bottle of cold-pressed mixed fruit juice, bright and refreshing for breakfast or on the go.', 'description' => 'No added concentrates, best served chilled within a few days of opening. Shake gently and refrigerate after opening. One bottle per unit for quick add-to-basket shopping. From Springwell beverages.', 'specs' => ['Volume' => '750 ml'], 'variants' => [
                ['Unit' => '750 ml', 'stock' => 22],
            ]],
            ['name' => 'Sparkling Water', 'category' => 'Beverages', 'brand' => 'Daily Goods', 'price' => 540, 'short' => 'Six-pack of 500 ml sparkling water bottles, crisp and unsweetened for meals and hydration.', 'description' => 'Each pack contains six bottles sized for lunch boxes, picnics, and fridge stocking. Store upright in a cool place away from direct sun. Add multiple packs to the cart when hosting or restocking. Everyday value from Daily Goods.', 'specs' => ['Pack' => '6 x 500 ml'], 'variants' => [
                ['Unit' => '6 pack', 'stock' => 15],
            ]],
            ['name' => 'Cotton Towel Set', 'category' => 'Household', 'brand' => 'Homestead', 'price' => 1650, 'featured' => true, 'compare' => 1900, 'short' => 'Four-piece cotton towel set with two bath towels and two hand towels in soft, absorbent weave.', 'description' => 'Washed cotton feels gentle on skin and dries quickly between uses. Ivory and sand colours coordinate with most bathrooms. Machine wash warm and tumble dry low to maintain loft. Homestead household essential at a special price while stock lasts.', 'specs' => ['Set' => '4 pieces', 'Material' => 'Cotton'], 'variants' => [
                ['Color' => 'Ivory', 'stock' => 8], ['Color' => 'Sand', 'stock' => 6],
            ]],
            ['name' => 'Hand Cream', 'category' => 'Personal Care', 'brand' => 'Harvest Row', 'price' => 480, 'short' => '75 ml tube of unscented hand cream, fast-absorbing for dry hands without heavy fragrance.', 'description' => 'Rich enough for winter dryness yet light enough for desk-side use throughout the day. Fragrance-free formula suits sensitive skin and shared households. Squeeze a pea-sized amount after washing and reapply as needed. From Harvest Row personal care.', 'specs' => ['Volume' => '75 ml'], 'variants' => [
                ['Unit' => '75 ml', 'stock' => 19],
            ]],
        ],
    ],
];
