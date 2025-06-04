# this script writes the tickers from each exchange into a text file in a list style, if you want a different output format let me know!

#!/usr/bin/env python3
"""
Fetch USDT tickers from Bybit, Binance Spot, Binance USDⓈ-M Futures
and MEXC Futures, then write each list to its own .txt file.

Usage:  python3 get_tickers.py
Dependencies: requests  (pip install requests)
"""

import requests
from pathlib import Path

# -------- helper -------------------------------------------------------------
def get_json(url: str, params: dict | None = None) -> dict | list:
    """Centralised HTTP-GET wrapper with basic error handling."""
    resp = requests.get(url, params=params, timeout=15)
    resp.raise_for_status()          # raises if HTTP status != 200
    return resp.json()

def write_txt(file_name: str, tickers: list[str]) -> None:
    """Write a list of symbols to a newline-delimited text file."""
    Path(file_name).write_text("\n".join(sorted(set(tickers))), encoding="utf-8")

# -------- BYBIT linear USDT futures -----------------------------------------
bybit_raw = get_json(
    "https://api.bybit.com/v5/market/tickers",          # v5 endpoint (not deprecated)  ✱
    params={"category": "linear"},
)
bybit_tickers = [
    item["symbol"] for item in bybit_raw["result"]["list"]
    if "USDT" in item["symbol"]
]
write_txt("bybit_tickers.txt", bybit_tickers)

# -------- BINANCE spot -------------------------------------------------------
binance_spot_raw = get_json("https://api.binance.com/api/v3/ticker/price")  # stable v3  ✱
binance_spot_tickers = [
    item["symbol"] for item in binance_spot_raw
    if (
        "USDT" in item["symbol"]
        and "_" not in item["symbol"]                # exclude composites
        and not any(suffix in item["symbol"]         # exclude leveraged pairs
                    for suffix in ("BEARUSDT", "BULLUSDT", "UPUSDT", "DOWNUSDT"))
    )
]
write_txt("binance_spot_tickers.txt", binance_spot_tickers)

# -------- BINANCE USDⓈ-M perpetual futures ----------------------------------
binance_fut_raw = get_json(
    "https://fapi.binance.com/fapi/v2/ticker/price"    # production v2 endpoint  ✱
)
binance_futures_tickers = [
    item["symbol"] for item in binance_fut_raw
    if "USDT" in item["symbol"] and "_" not in item["symbol"]
]
write_txt("binance_futures_tickers.txt", binance_futures_tickers)

# -------- MEXC USDT perpetual futures ---------------------------------------
mexc_raw = get_json("https://contract.mexc.com/api/v1/contract/detail")      # v1 still current  ✱
mexc_futures_tickers = [
    item["symbol"].replace("_", "")                   # strip underscore for consistency
    for item in mexc_raw["data"]
    if "USDT" in item["symbol"]
]
write_txt("mexc_futures_tickers.txt", mexc_futures_tickers)

print("Ticker files written successfully.")
