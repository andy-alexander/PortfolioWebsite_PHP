<?php

//include("databaseClass.php"); // already included in login.php
include("StockDownloader.php");

// class holds the user portfolio with stock positions and cash
class Portfolio
{
	private $id_acct;
	private $cash_balance;
	private $positions;

	// retrieve portfolio from the database
	public function __construct()
	{
		global $db;
		$query = "SELECT id_acct, cash_balance FROM trading_accounts WHERE id_user = ".$_SESSION['id_user'];
		$res = $db->send_sql($query);
		if(mysql_num_rows($res) > 0)
		{
			$row = mysql_fetch_row($res);
			$this->id_acct = $row[0];
			$this->cash_balance = $row[1];
		}
		else
		{
			$query = "INSERT INTO trading_accounts (id_user) VALUES (".$_SESSION["id_user"].")";
			$db->send_sql($query);
			$this->id_acct = $db->insert_id();
			$this->cash_balance = 0;
		}
		$query = "SELECT s.symbol, sp.shares FROM stocks AS s, stock_in_portfolio AS sp WHERE sp.id_acct = ".$this->id_acct." AND s.id_stock = sp.id_stock";
		$res = $db->send_sql($query);
		$this->positions = array();
		$stocks = array();
		$i = 0;
		while($row = mysql_fetch_row($res))
		{
			$sym = $row[0];
			$this->positions[$sym] = $row[1];
			$stocks[$i++] = $sym;
		}
		$stocks = StockDownloader::download($stocks);
		foreach($stocks as $stock)
			$this->positions[$stock->symbol()] = new Position($stock, $this->positions[$stock->symbol()]);
	}

	// add stock transaction to the portfolio and database and returns true if successful
	public function addStockTransaction($symbol, $shares, $price)
	{
		global $db;
		$this->cash_balance -= $shares * $price;
		$query = "UPDATE trading_accounts SET cash_balance=".$this->cash_balance." WHERE id_acct=".$this->id_acct;
		if(!$db->send_sql($query))
			return false;
		$query = "SELECT id_stock FROM stocks WHERE symbol='".$symbol."'";
		$res = $db->send_sql($query);
		$row = mysql_fetch_row($res);
		$id_stock = $row[0];
		if(isset($this->positions[$symbol]))
		{
			$pos = $this->positions[$symbol];
			$shares += $pos->getShares();
			if($shares > 0)
			{
				$pos->setShares($shares);
				$query = "UPDATE stock_in_portfolio SET shares=".$shares." WHERE id_stock=".$id_stock;
				return $db->send_sql($query);
			}
			else
			{
				unset($this->positions[$symbol]);
				$query = "DELETE FROM stock_in_portfolio WHERE id_stock=".$id_stock;
				return $db->send_sql($query);
			}
		}
		else
		{
			$query = "INSERT INTO stock_in_portfolio (id_acct, id_stock, shares) VALUES (".$this->id_acct.", ".$id_stock.", ".$shares.")";
			return $db->send_sql($query);
		}
	}

	// add cash transaction to the portfolio and database and returns true if successful
	public function addCashTransaction($amount)
	{
		global $db;
		$this->cash_balance += $amount;
		$query = "UPDATE trading_accounts SET cash_balance=".$this->cash_balance." WHERE id_acct=".$this->id_acct;
		return $db->send_sql($query);
	}

	// return cash balance as a string
	public function cashBalance()
	{
		return number_format($this->cash_balance, 2);
	}

	// return cash balance as a float
	public function cashBalanceNumeric()
	{
		return $this->cash_balance;
	}

	public function numShares($symbol)
	{
		if(isset($this->positions[$symbol]))
			return $this->positions[$symbol]->getShares();
		else
			return 0;
	}

	// return portfolio value as a string
	public function value()
	{
		return number_format($this->valueNumeric(), 2);
	}

	// return portfolio value as a float
	public function valueNumeric()
	{
		$val = $this->cash_balance;
		foreach($this->positions as $pos)
			$val += $pos->valueNumeric();
		return $val;
	}

	// return portfolio change as a string
	public function change()
	{
		return number_format($this->changeNumeric(), 2);
	}

	// return portfolio change as a float
	public function changeNumeric()
	{
		$change = 0;
		foreach($this->positions as $pos)
			$change += $pos->valueChangeNumeric();
		return $change;
	}

	public function percentChange()
	{

		$change = $this->changeNumeric();
		$prevValue = $this->valueNumeric() - $change;
		if($prevValue == 0)
			if($change == 0)
				return number_format(0, 2);
			else
				return "N/A";
		return number_format(100 * $change / $prevValue, 2);
	}

	// return the change and percent as a color-coded string
	public function getChange()
	{
		$change = $this->change();
		if($change > 0)
			return "<span class=\"gain\">".$change." (".$this->percentChange()."%)</span>";
		else if($change < 0)
			return "<span class=\"loss\">".$change." (".$this->percentChange()."%)</span>";
		else
			return $change." (".$this->percentChange()."%)";
	}

	// echo a table display of the portfolio
	public function display()
	{
		echo "<table class=\"mypad\">\n";
		echo "<tr><th>Symbol</th><th>Shares</th><th>Price</th><th>Change</th><th>Value</th><th>Change</th></tr>\n";
		foreach($this->positions as $pos)
			echo "<tr><td>".$pos->symbol()."</td><td>".$pos->getShares()."</td><td>".$pos->price()."</td><td>".$pos->getPriceChange()."</td><td>".$pos->value()."</td><td>".$pos->getChange()."</td></tr>\n";
		echo "<tr><td colspan=\"4\">Cash</td><td colspan=\"2\">".$this->cashBalance()."</td></tr>\n";
		echo "<tr><th colspan=\"4\">Total</th><td>".$this->value()."</td><td>".$this->getChange()."</td></tr>\n";
		echo "</table>\n";
	}
}

// class for a position in a single stock
class Position
{
	private $stock;
	private $shares;

	public function __construct($stock, $shares)
	{
		$this->stock = $stock;
		$this->shares = $shares;
	}

	public function symbol()
	{
		return $this->stock->symbol();
	}

	public function getShares()
	{
		return $this->shares;
	}

	public function setShares($shares)
	{
		$this->shares = $shares;
	}

	public function price()
	{
		return $this->stock->lastTrade();
	}

	// return the value of the position as a string
	public function value()
	{
		return number_format($this->shares * $this->stock->lastTrade(), 2);
	}

	// return the value of the position as a float
	public function valueNumeric()
	{
		return $this->shares * $this->stock->lastTrade();
	}

	public function priceChange()
	{
		return $this->stock->change();
	}

	// return the stock price change as a color-coded string
	public function getPriceChange()
	{
		$change = $this->priceChange();
		if($change > 0)
			return "<span class=\"gain\">".$change."</span>";
		else if($change < 0)
			return "<span class=\".loss\">".$change."</span>";
		else
		return $change;
	}

	// return the value change of the position as a string
	public function valueChange()
	{
		return number_format($this->shares * $this->stock->change(), 2);
	}

	// return the value change of the position as a float
	public function valueChangeNumeric()
	{
		return $this->shares * $this->stock->change();
	}

	public function percentChange()
	{
		return $this->stock->percentChange();
	}

	// return the change and percent as a color-coded string
	public function getChange()
	{
		$change = $this->valueChange();
		if($change > 0)
			return "<span class=\"gain\">".$change." (".$this->percentChange()."%)</span>";
		else if($change < 0)
			return "<span class=\"loss\">".$change." (".$this->percentChange()."%)</span>";
		else
			return $change." (".$this->percentChange()."%)";
	}
}

?>