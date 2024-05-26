# bitcoin-web
Frontend for bitcoin-cli written in PHP/JS.

Requirements: 
      configured web server, 
      php, php-curl,
      bitcoin core installed and preconfigured (see example configuration)

Developed and maintained by diavolic (diavolic@gmail.com)

config.ini variables explanation:

btc_server_ip = IP address where BTC daemon is running
btc_server_port = RPC Port
btc_rpc_user = "RPC_user"
btc_rpc_password = "$tr0ng_passw0rd"
exchange_rate = Where should we get current exchange rate from, available values are : "bitfinex" , "binance" , "bitstamp"
refresh_interval = Update interval for tables should(in seconds)
log_portion = How much last lines we want to see from bitcojn daemon log 

internal_* variables needed just to store some information, to avoid using databases
