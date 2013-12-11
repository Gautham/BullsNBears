BullsNBears
===========

An SDK developed from the Virtual Stock Market Exchange, Bulls N Bears conducted by NIT Calicut during the annual tech fest, Tathva.

License
--------
The BnB SDK is currently offered under the GNU GENERAL PUBLIC LICENSE v3. This means that you may use our source and modify it to your needs but major changes to source code should be made open-source. Although we do not require you to publicize the use of our SDK, we would like to know if you're using it & any suggestions you have. Contributions via Pull requests are always welcome!

How To Setup
-------------------- 

1. Create a New Database & User in MySQL.
2. Import the Database structure from Initialize.sql
3. Add the relavent Database Details to includes/config.ini
4. Add the Stock data in Stocks page using a Data Source Of Your Choice.
5. To make use of the SDK, include BnB.php at the top of your code using 
		require_once("BnB.php");


How To Run A Game
----------------------

1. The normal transactions and scheduling may be done by utilizing the Player class to perform necessary functions.
2. Stock data should be updated regularly by parsing the data for stocks from any suitable source (atleast once every 2 minutes during runtime).
3. Stock Data updating should be immediately followed by running perp/ValueUpdate.php which will run all Scheduled Transactions and update Market Value.
3. At the start of each day before market opens, run perp/DayUpdate.php to reset the Present Day's earnings.
4. At the end of each day after market closes, run perp/ShortUpdate.php to cover all the shorted stocks.
5. At the start of each week before market opens, run perp/WeekUpdate.php to reset the Present Week's earnings.
6. perp/Sim.php is a special function that can be used to replay the game based on stored History in the event that some bug occurs during runtime.
7. perp/RemoveStock.php can be used to remove a stock thats gone out of the market. It will automatically sell the stocks at the last known value.

Contributors
-------------
[*] Ashwin Lakshmanan                                ashwinner92@gmail.com
[*] Gautham R Warrier                                gautham.r.w@gmail.com
[*] Pranav Ashok                                     pranavashok@gmail.com
[*] Shamil CM                                        shamil.cm@gmail.com
[*] Sreeraj S                                        sreeraj.altair@gmail.com
[*] Vivek Anand T Kallampally                        vivekzhere@gmail.com