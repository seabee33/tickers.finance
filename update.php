<?php
$host = 'localhost';
$database = 'database_name';
$user = 'username';
$password = 'password';

// Create a connection to the database
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_GET["user"]) and $_GET["user"] == "rtfy7986ttfgyu"){
    print("updating...");
} else {
    print("Not authenticated");
    exit;
}

try {
    // Create the table if it doesn't exist
    $sql = "
        CREATE TABLE IF NOT EXISTS tickers (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            exchange VARCHAR(32),
            category VARCHAR(32),
            ticker VARCHAR(64),
            UNIQUE(exchange, category, ticker)
        )";
    $conn->query($sql);

    // Function to perform HTTP GET request
    function getJsonFromUrl($url, $params = []) {
        $ch = curl_init();
        $queryString = http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $queryString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    // Bybit futures
    $bybit_futures = getJsonFromUrl("https://api.bybit.com/v5/market/tickers", ["category" => "linear"]);
    $data = $bybit_futures["result"]["list"];
    foreach ($data as $item) {
        if (strpos($item["symbol"], "USDT") !== false) {
            $stmt = $conn->prepare("INSERT IGNORE INTO tickers (exchange, category, ticker) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $exchange, $category, $ticker);
            $exchange = 'bybit';
            $category = 'futures';
            $ticker = $item["symbol"];
            $stmt->execute();
        }
    }
    $conn->commit();

    // Binance spot market
    $spot_response = getJsonFromUrl("https://api.binance.com/api/v3/ticker/price");
    foreach ($spot_response as $item) {
        if (strpos($item['symbol'], "USDT") !== false && strpos($item['symbol'], "_") === false) {
            $stmt = $conn->prepare("INSERT IGNORE INTO tickers (exchange, category, ticker) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $exchange, $category, $ticker);
            $exchange = 'binance';
            $category = 'spot';
            $ticker = $item["symbol"];
            $stmt->execute();
        }
    }
    $conn->commit();

    // USD(s)M Futures
    $usdsm_futures = getJsonFromUrl("https://testnet.binancefuture.com/fapi/v2/ticker/price");
    foreach ($usdsm_futures as $item) {
        if (strpos($item['symbol'], "USDT") !== false && strpos($item['symbol'], "_") === false) {
            $stmt = $conn->prepare("INSERT IGNORE INTO tickers (exchange, category, ticker) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $exchange, $category, $ticker);
            $exchange = 'binance';
            $category = 'usdsm';
            $ticker = $item["symbol"];
            $stmt->execute();
        }
    }
    $conn->commit();

} catch (mysqli_sql_exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if ($conn->ping()) {
        $conn->close();
        echo "Done";
    }
}
?>
