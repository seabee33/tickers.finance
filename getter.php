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

if(isset($_GET["q"])){
	$isFutures = False;
    switch($_GET["q"]){

        // BINANCE
        case "binance-spot":
            $exchange = "binance";
            $exchangeResult = "BINANCE";
            $category = "spot";
            break;
        case "binance-futures":
            $exchange = "binance";
            $exchangeResult = "BINANCE";
            $category = "usdsm";
	    $isFutures = True;
            break;
        
        // BYBIT
        case "bybit-futures":
            $exchange = "bybit";
            $exchangeResult = "BYBIT";
            $category = "futures";
			$isFutures = True;
            break;

        // MEXC
        case "mexc-futures":
            $exchange = "mexc";
            $exchangeResult = "MEXC";
            $category = "futures";
			$isFutures = True;
            break;
        case "mexc-spot":
            $exchange = "mexc";
            $exchangeResult = "MEXC";
            $category = "spot";
			$isFutures = True;
            break;
        default:
            die("Something broke");
    }
    $result = $conn->query("SELECT ticker FROM tickers WHERE exchange='$exchange' AND category='$category'");

    if(!$result){
        die("query failed, error: " . $conn->error);
    }

    $tickers = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $ticker = $row["ticker"];
            $tickers[] = $exchangeResult . ":" . $ticker . ($isFutures ? ".P" : "");
		}
    }
    echo implode(",", $tickers);
    $conn->close();
}

?>
