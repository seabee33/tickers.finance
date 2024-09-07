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

if ($_GET["q"] == "binance-spot"){
	$result = $conn->query("SELECT ticker FROM tickers WHERE exchange='binance' AND category='spot'");
} elseif($_GET["q"] == "binance-futures"){
	$result = $conn->query("SELECT ticker FROM tickers WHERE exchange='binance' AND category='usdsm'");
} elseif($_GET["q"] == "bybit-futures"){
	$result = $conn->query("SELECT ticker FROM tickers WHERE exchange='bybit' AND category='futures'");
} else {
	die("Dam, something broke :(");
}

$tickers = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
		$ticker = $row["ticker"];
        if ($_GET['q'] == "binance-spot"){
			$tickers[] = "BINANCE:{$ticker}";
		} elseif($_GET['q'] == "binance-futures"){
			$tickers[] = "BINANCE:{$ticker}.P";
		} elseif($_GET['q'] == "bybit-futures"){
			$tickers[] = "BYBIT:{$ticker}.P";
		}
    }
}

echo implode(",", $tickers);

$conn->close();
?>
