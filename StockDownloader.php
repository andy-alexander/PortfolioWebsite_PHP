<?php

class StockDownloader
{
	// takes an array of stock symbols, downloads information, and returns an array of Stocks
	public static function download($symbols)
	{
		$stocks = array();
		if(count($symbols) > 0)
		{
			$query = "http://download.finance.yahoo.com/d/quotes.csv?s=";
			foreach($symbols as $symbol)
				$query = $query.$symbol."+";
			$query = substr($query, 0, strlen($query) - 1);

			// determine whether to download full quote or just price
			if(preg_match("/.*quotes.php/i", $_SERVER['SCRIPT_NAME']))
			{
				$query = $query."&f=snl1b3b2pohgva2t8kjj1red";
				if(!$fp = fopen($query, "r"))
					return false;
				for($i = 0; $fields = fgetcsv($fp); $i++)
				{
					$headlines = "";
					$page = file_get_contents("http://finance.yahoo.com/q?s=".$symbols[$i]);
					if(preg_match("/>Headlines<\/h.*(<ul>.*<\/ul>)[\s\S]*More[\s]Headlines/", $page, $matches))
						$headlines = $matches[1];
					$stocks[$i] = new Stock($fields, $headlines);
				}
				fclose($fp);
			}
			else
			{
				$query = $query."&f=sl1p";
				if(!$fp = fopen($query, "r"))
					return stocks;
				for($i = 0; $fields = fgetcsv($fp); $i++)
					$stocks[$i] = new Stock($fields, false);
				fclose($fp);
			}
		}
		return $stocks;
	}
}

// class contains a stock quote
class Stock
{
	private $symbol;
	private $name;
	private $lastTrade;
	private $bid;
	private $ask;
	private $prevClose;
	private $open;
	private $high;
	private $low;
	private $volume;
	private $avgVolume;
	private $oneYearTarget;
	private $yearHigh;
	private $yearLow;
	private $marketCap;
	private $peRatio;
	private $eps;
	private $dividend;
	private $headlines;

	// create a quote from an array and string of headline links
	public function __construct($fields, $headlines)
	{
		// full quote
		if($headlines)
		{
			$this->symbol = $fields[0];
			$this->name = $fields[1];
			$this->lastTrade = $fields[2];
			$this->bid = $fields[3];
			$this->ask = $fields[4];
			$this->prevClose = $fields[5];
			$this->open = $fields[6];
			$this->high = $fields[7];
			$this->low = $fields[8];
			$this->volume = $fields[9];
			$this->avgVolume = $fields[10];
			$this->oneYearTarget = $fields[11];
			$this->yearHigh = $fields[12];
			$this->yearLow = $fields[13];
			$this->marketCap = $fields[14];
			$this->peRatio = $fields[15];
			$this->eps = $fields[16];
			$this->dividend = $fields[17];
			$this->headlines = $headlines;
		}
		// just price
		else
		{
			$this->symbol = $fields[0];
			$this->lastTrade = $fields[1];
			$this->prevClose = $fields[2];
		}
	}

	public function symbol()
	{
		return $this->symbol;
	}

	public function name()
	{
		return $this->name;
	}

	public function lastTrade()
	{
		return $this->lastTrade;
	}

	public function change()
	{
		return number_format($this->lastTrade - $this->prevClose, 2);
	}

	public function percentChange()
	{
		return number_format(100 * ($this->lastTrade - $this->prevClose) / $this->prevClose, 2);
	}

	public function bid()
	{
		return $this->bid;
	}

	public function ask()
	{
		return $this->ask;
	}

	public function prevClose()
	{
		return $this->prevClose;
	}

	public function open()
	{
		return $this->open;
	}

	public function high()
	{
		return $this->high;
	}

	public function low()
	{
		return $this->low;
	}

	public function volume()
	{
		return $this->volume;
	}

	public function avgVolume()
	{
		return $this->avgVolume;
	}

	public function oneYearTarget()
	{
		return $this->oneYearTarget;
	}

	public function yearHigh()
	{
		return $this->yearHigh;
	}

	public function yearLow()
	{
		return $this->yearLow;
	}

	public function marketCap()
	{
		return $this->marketCap;
	}

	public function peRatio()
	{
		return $this->peRatio;
	}

	public function eps()
	{
		return $this->eps;
	}

	public function dividend()
	{
		return $this->dividend;
	}

	public function yield()
	{
		return number_format(100 * $this->dividend / $this->prevClose, 2);
	}

	public function headlines()
	{
		return $this->headlines;
	}

	// display a table with stock quote and headlines
	public function display()
	{
		if($this->prevClose == "N/A")
			echo "<h3>There are no stocks with symbol ".$this->symbol()."</h3>\n";
		else
		{
			echo "<table class=\"mypad\">";
				echo "<tr>";
				echo "<th>Name (Symbol)</th><th>Price</th><th>Change(%)</th>";
				echo "</tr>";
				echo "<tr>";
				echo "<td>".$this->name." (".$this->symbol.")</td><td>".$this->lastTrade."</td><td>".$this->getChange()."</td>";
				echo "</tr>";
				echo "</table>";

			echo "<table border=1>\n";
			echo "<tr>\n";
			echo "<td>Prev Close: ".$this->prevClose."</td><td>One-Year Target: ".$this->oneYearTarget."</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Open: ".$this->open."</td><td>52-Week Range: ".$this->yearLow." - ".$this->yearHigh."</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Day's Range: ".$this->low." - ".$this->high."</td><td>Market Cap: ".$this->marketCap."</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Bid-Ask: ".$this->bid." - ".$this->ask."</td><td>P/E: ".$this->peRatio."</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Volume: ".$this->volume."</td><td>EPS: ".$this->eps."</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Avg Volume: ".$this->avgVolume."</td><td>Dividend: ".$this->dividend." (".$this->yield()."%)</td>\n";
			echo "</tr>\n";
			echo "</table>\n";
			if(strlen($this->headlines) > 0)
			{
				echo "<br/>\n";
				echo "<h3>Headlines</h3>\n";
				echo $this->headlines;
			}
		}
	}

	public function getChange()
	{
		$change = $this->change();
		if($change > 0)
			return "<span class=\"gain\">".$change." (".$this->percentChange()."%)</span>";
		else if($change < 0)
			return "<span class=\"loss\">".$change." (".$this->percentChange()."%)</span>";
		else
			return $change." (".$this->percentChange().")";
	}

	// display a row for this stock in the watchlist table
	public function displaywatchlist()
	{
		echo "<tr>";
		echo "<td>
			<a href=\"Watchlist.php?removesymbol=".$this->symbol."\">
			<img src=\"remove.png\" alt=\"Remove symbol\"/>
			</a></td>";
		echo "<td><a href=\"quotes.php?stock=".$this->symbol."\">".$this->symbol."<a/></td><td>".$this->lastTrade."</td><td>".$this->getChange()."</td>";
		echo "</tr>";
		//echo "</table>\n";
	}
}

?>