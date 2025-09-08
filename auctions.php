<?php
session_start();
date_default_timezone_set('Africa/Nairobi'); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

/* ✅ Get distinct breeds for filters */
$breeds = $conn->query("SELECT DISTINCT breed FROM cattle ORDER BY breed ASC");

/* ✅ Build query with filters */
$sql = "SELECT c.* 
        FROM cattle c 
        WHERE 1=1";

/* ✅ Search filter */
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql .= " AND (c.name LIKE '%$search%' 
                OR c.breed LIKE '%$search%' 
                OR c.weight LIKE '%$search%')";
}

if (!empty($_GET['breed'])) {
    $breed = $conn->real_escape_string($_GET['breed']);
    $sql .= " AND c.breed = '$breed'";
}
if (!empty($_GET['age_min']) && is_numeric($_GET['age_min'])) {
    $sql .= " AND c.age >= " . intval($_GET['age_min']);
}
if (!empty($_GET['age_max']) && is_numeric($_GET['age_max'])) {
    $sql .= " AND c.age <= " . intval($_GET['age_max']);
}
if (!empty($_GET['price_min']) && is_numeric($_GET['price_min'])) {
    $sql .= " AND c.price >= " . intval($_GET['price_min']);
}
if (!empty($_GET['price_max']) && is_numeric($_GET['price_max'])) {
    $sql .= " AND c.price <= " . intval($_GET['price_max']);
}

$sql .= " ORDER BY c.start_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Auction - All Cattle</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; padding: 0; }
        h2 { text-align: center; color: darkgreen; margin-top: 20px; }
        .container { display: flex; margin: 20px; gap: 20px; }

        /* Sidebar filters */
        .sidebar {
            width: 250px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .sidebar h3 { margin-bottom: 10px; color: darkgreen; }
        .sidebar label { display: block; margin: 5px 0 3px; font-size: 14px; }
        .sidebar input, .sidebar select {
            width: 100%; padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 5px;
        }
        .filter-btn, .reset-btn {
            border: none; padding: 10px;
            width: 100%; border-radius: 5px;
            cursor: pointer;
            margin-bottom: 5px;
        }
        .filter-btn { background: green; color: white; }
        .filter-btn:hover { background: darkgreen; }
        .reset-btn { background: #ccc; color: #333; }
        .reset-btn:hover { background: #999; }

        /* Grid display */
        .grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: #fff; padding: 15px;
            border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        img { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; }
        .price { font-size: 18px; font-weight: bold; color: darkred; margin: 5px 0; }
        .status { font-weight: bold; }
        .active { color: green; }
        .closed { color: red; }
        .buy-btn {
            margin-top: 10px;
            background: green; color: white;
            border: none; padding: 8px 15px;
            border-radius: 5px; cursor: pointer;
        }
        .buy-btn:hover { background: darkgreen; }
        .buy-btn:disabled {
            background: #ccc; color: #666;
            cursor: not-allowed;
        }
    </style>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h2>Auction - All Uploaded Cattle</h2>

    <div class="container">
        <!-- Sidebar Filters -->
        <div class="sidebar">
            <h3>🔍 Filter & Search Cattle</h3>
            <form method="GET">
                <!-- ✅ Search box -->
                <input type="text" name="search" placeholder="Search by name, breed, or weight..." 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

                <label>Breed</label>
                <select name="breed">
                    <option value="">Any</option>
                    <?php while($b = $breeds->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($b['breed']) ?>" 
                            <?= (($_GET['breed'] ?? '') == $b['breed']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['breed']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Age Range</label>
                <input type="number" name="age_min" placeholder="Min" value="<?= htmlspecialchars($_GET['age_min'] ?? '') ?>">
                <input type="number" name="age_max" placeholder="Max" value="<?= htmlspecialchars($_GET['age_max'] ?? '') ?>">

                <label>Price Range (Ksh)</label>
                <input type="number" name="price_min" placeholder="Min" value="<?= htmlspecialchars($_GET['price_min'] ?? '') ?>">
                <input type="number" name="price_max" placeholder="Max" value="<?= htmlspecialchars($_GET['price_max'] ?? '') ?>">

                <button type="submit" class="filter-btn">Apply Filters</button>
                <a href="auctions.php"><button type="button" class="reset-btn">Reset Filters</button></a>
            </form>
        </div>

        <!-- Cattle Grid -->
        <div class="grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                    $expired = (strtotime($row['close_time']) <= time()); 
                ?>
                    <div class="card">
                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Cattle">
                        
                        <p><b>Breed:</b> <?= htmlspecialchars($row['breed']) ?></p>
                        <p><b>Age:</b> <?= $row['age'] ?> years</p>
                        <p><b>Weight:</b> <?= $row['weight'] ?> kg</p>
                        <p class="price">Ksh <?= number_format($row['price'], 2) ?></p>

                        <p class="status <?= $expired ? 'closed' : 'active' ?>">
                            <?= $expired ? "Closed" : "Active (closes " . date("d M Y H:i", strtotime($row['close_time'])) . ")" ?>
                        </p>

                        <?php if (!$expired): ?>
                            <a href="bid.php?id=<?= $row['id']; ?>">
                                <button class="buy-btn">Place Bid</button>
                            </a>
                        <?php else: ?>
                            <button class="buy-btn" disabled>Closed</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:red;">No cattle found with selected filters.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-refresh every 60 seconds (1 minute)
        setInterval(() => location.reload(), 60000);
    </script>
</body>
</html>
