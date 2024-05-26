# bitcoin-web
Frontend for bitcoin-cli written in PHP/JS.

Requirements: 
      configured web server, 
      php, php-curl,
      bitcoin core installed and preconfigured (see example configuration)

Developed and maintained by diavolic (diavolic@gmail.com)

config.ini variables explanation:

btc_server_ip = IP address where BTC daemon is running<br>
btc_server_port = RPC Port<br>
btc_rpc_user = "RPC_user"<br>
btc_rpc_password = "$tr0ng_passw0rd"<br>
exchange_rate = Where should we get current exchange rate from, available values are : "bitfinex" , "binance" , "bitstamp"<br>
refresh_interval = Update interval for tables should(in seconds)<br>
log_portion = How much last lines we want to see from bitcojn daemon log <br>

internal_* variables needed just to store some information, to avoid using databases
