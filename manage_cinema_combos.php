<?php
// Cinema Combo Management
// This file can be used to add/edit cinema combos

require_once 'includes/config.php';

// Sample cinema combos data
$sample_combos = [
    [
        'name' => 'Combo Action Movie',
        'description' => 'Combo đặc biệt cho phim hành động với đồ ăn nhẹ và cocktail mạnh',
        'price' => 800000,
        'movie_id' => null, // General combo
        'items' => [
            ['menu_item_id' => 1, 'quantity' => 2], // Popcorn
            ['menu_item_id' => 2, 'quantity' => 2], // Nachos
            ['menu_item_id' => 3, 'quantity' => 2], // Beer
            ['menu_item_id' => 4, 'quantity' => 1], // Whiskey
        ]
    ],
    [
        'name' => 'Combo Romantic Movie',
        'description' => 'Combo lãng mạn với chocolate và cocktail ngọt',
        'price' => 650000,
        'movie_id' => null,
        'items' => [
            ['menu_item_id' => 5, 'quantity' => 1], // Chocolate
            ['menu_item_id' => 6, 'quantity' => 2], // Wine
            ['menu_item_id' => 7, 'quantity' => 1], // Cheese platter
        ]
    ],
    [
        'name' => 'Combo Family Movie',
        'description' => 'Combo gia đình với đồ ăn nhẹ và nước uống cho mọi lứa tuổi',
        'price' => 500000,
        'movie_id' => null,
        'items' => [
            ['menu_item_id' => 1, 'quantity' => 3], // Popcorn
            ['menu_item_id' => 8, 'quantity' => 4], // Soft drinks
            ['menu_item_id' => 9, 'quantity' => 2], // Cookies
        ]
    ],
    [
        'name' => 'Combo Horror Movie',
        'description' => 'Combo kinh dị với đồ ăn cay và cocktail mạnh',
        'price' => 750000,
        'movie_id' => null,
        'items' => [
            ['menu_item_id' => 10, 'quantity' => 2], // Spicy snacks
            ['menu_item_id' => 11, 'quantity' => 2], // Tequila
            ['menu_item_id' => 12, 'quantity' => 1], // Energy drink
        ]
    ]
];

// Function to insert combo
function insertCombo($pdo, $combo) {
    try {
        $pdo->beginTransaction();
        
        // Insert combo
        $combo_stmt = $pdo->prepare("
            INSERT INTO cinema_combos (name, description, price, movie_id, is_available, sort_order, created_at) 
            VALUES (?, ?, ?, ?, 1, 0, NOW())
        ");
        
        $combo_stmt->execute([
            $combo['name'],
            $combo['description'],
            $combo['price'],
            $combo['movie_id']
        ]);
        
        $combo_id = $pdo->lastInsertId();
        
        // Insert combo items
        if (!empty($combo['items'])) {
            $item_stmt = $pdo->prepare("
                INSERT INTO cinema_combo_items (combo_id, menu_item_id, quantity, sort_order, created_at) 
                VALUES (?, ?, ?, 0, NOW())
            ");
            
            foreach ($combo['items'] as $item) {
                $item_stmt->execute([
                    $combo_id,
                    $item['menu_item_id'],
                    $item['quantity']
                ]);
            }
        }
        
        $pdo->commit();
        return $combo_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Function to add sample movies
function addSampleMovies($pdo) {
    $sample_movies = [
        [
            'title' => 'Avengers: Endgame',
            'genre' => 'Action',
            'duration' => 181,
            'language' => 'English',
            'subtitle' => 'Vietnamese',
            'minimum_spend_per_person' => 500000,
            'poster' => 'assets/images/avengers-endgame.jpg',
            'description' => 'Phim siêu anh hùng Marvel với cốt truyện hấp dẫn'
        ],
        [
            'title' => 'Spider-Man: No Way Home',
            'genre' => 'Action',
            'duration' => 148,
            'language' => 'English',
            'subtitle' => 'Vietnamese',
            'minimum_spend_per_person' => 450000,
            'poster' => 'assets/images/spiderman-nowayhome.jpg',
            'description' => 'Cuộc phiêu lưu mới của Spider-Man'
        ],
        [
            'title' => 'Top Gun: Maverick',
            'genre' => 'Action',
            'duration' => 131,
            'language' => 'English',
            'subtitle' => 'Vietnamese',
            'minimum_spend_per_person' => 400000,
            'poster' => 'assets/images/topgun-maverick.jpg',
            'description' => 'Phim hành động hàng không đầy kịch tính'
        ],
        [
            'title' => 'The Batman',
            'genre' => 'Action',
            'duration' => 176,
            'language' => 'English',
            'subtitle' => 'Vietnamese',
            'minimum_spend_per_person' => 550000,
            'poster' => 'assets/images/the-batman.jpg',
            'description' => 'Phiên bản mới của Batman với Robert Pattinson'
        ],
        [
            'title' => 'Dune',
            'genre' => 'Sci-Fi',
            'duration' => 155,
            'language' => 'English',
            'subtitle' => 'Vietnamese',
            'minimum_spend_per_person' => 600000,
            'poster' => 'assets/images/dune.jpg',
            'description' => 'Phim khoa học viễn tưởng đầy ấn tượng'
        ]
    ];
    
    $movie_stmt = $pdo->prepare("
        INSERT INTO movies (title, genre, duration, language, subtitle, minimum_spend_per_person, poster, description, is_available, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    
    foreach ($sample_movies as $movie) {
        $movie_stmt->execute([
            $movie['title'],
            $movie['genre'],
            $movie['duration'],
            $movie['language'],
            $movie['subtitle'],
            $movie['minimum_spend_per_person'],
            $movie['poster'],
            $movie['description']
        ]);
    }
}

// Function to add sample menu items for combos
function addSampleMenuItems($pdo) {
    $sample_items = [
        ['name' => 'Popcorn Caramel', 'price' => 50000, 'category' => 'snacks'],
        ['name' => 'Nachos Cheese', 'price' => 80000, 'category' => 'snacks'],
        ['name' => 'Beer Tiger', 'price' => 45000, 'category' => 'drinks'],
        ['name' => 'Whiskey Johnnie Walker', 'price' => 200000, 'category' => 'drinks'],
        ['name' => 'Chocolate Box', 'price' => 120000, 'category' => 'desserts'],
        ['name' => 'Wine Red', 'price' => 150000, 'category' => 'drinks'],
        ['name' => 'Cheese Platter', 'price' => 180000, 'category' => 'snacks'],
        ['name' => 'Soft Drink', 'price' => 30000, 'category' => 'drinks'],
        ['name' => 'Cookies Mix', 'price' => 60000, 'category' => 'desserts'],
        ['name' => 'Spicy Chips', 'price' => 40000, 'category' => 'snacks'],
        ['name' => 'Tequila Shot', 'price' => 80000, 'category' => 'drinks'],
        ['name' => 'Energy Drink', 'price' => 35000, 'category' => 'drinks']
    ];
    
    $item_stmt = $pdo->prepare("
        INSERT INTO menu_items (name, price, category, description, is_available, created_at) 
        VALUES (?, ?, ?, '', 1, NOW())
    ");
    
    foreach ($sample_items as $item) {
        $item_stmt->execute([
            $item['name'],
            $item['price'],
            $item['category']
        ]);
    }
}

// Usage instructions
echo "Cinema Combo Management\n";
echo "======================\n\n";
echo "This file contains sample data for cinema combos and movies.\n";
echo "To use this data:\n\n";
echo "1. Run the SQL file first to create the database tables\n";
echo "2. Uncomment the code below to insert sample data\n";
echo "3. Modify the sample data as needed\n\n";

// Uncomment the following lines to insert sample data
/*
try {
    $pdo = getDBConnection();
    
    // Add sample menu items first
    addSampleMenuItems($pdo);
    echo "Sample menu items added successfully!\n";
    
    // Add sample movies
    addSampleMovies($pdo);
    echo "Sample movies added successfully!\n";
    
    // Add sample combos
    foreach ($sample_combos as $combo) {
        $combo_id = insertCombo($pdo, $combo);
        echo "Combo '{$combo['name']}' added with ID: $combo_id\n";
    }
    
    echo "\nAll sample data added successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
*/
?>
