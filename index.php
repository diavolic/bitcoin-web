<?php
	include_once ("config.php");

	$rate = $vars->getRate();

	if ($vars->isAct("balance")) {
		$j = $btc->getbalance();
		$feeHigh = $btc->estimatesmartfee(6);
		$feeLow = $btc->estimatesmartfee(12, "ECONOMICAL");
		$feeEco = $btc->estimatesmartfee(36);
		$vars->finishAct(array("rate" => $vars->getRate(), "balance" => $j, "fee" => array("high" => sprintf("%.8f",$feeHigh["feerate"]), "low" => sprintf("%.8f",$feeLow["feerate"]), "eco" => sprintf("%.8f",$feeEco["feerate"])), "new" => true /*, "pending" => $j["untrusted_pending"]*/));
	}

	if ($vars->isAct("request")) {
		$type = $vars->cleanp("type");
		if (in_array($type, array("legacy", "p2sh-segwit", "bech32"))) {
			if ($address = $btc->getnewaddress("", $type)) {
				if ($vars->cleanp("save") == "yes") {
					$vars->book_request[$address] = $vars->cleanp("comment");
					$vars->write();
				}
				$vars->finishAct(array("address" => $address, "qr" => "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=bitcoin:{$address}"));
			} else {
				$vars->finishAct($btc->error);
			}
		} else {
			$vars->finishAct("Invalid address type");
		}
	}


	if ($vars->isAct("send")) {
		$type = $vars->cleanp("type");
		$rpl = $vars->cleanp("replace") == "yes";

		if (!$btc->settxfee($vars->cleanp("fee"))) {
			$vars->finishAct($btc->error);
		} 
		$btc->walletpassphrase($vars->cleanp("password"), 30);
		if ($btc->error) {
			$vars->finishAct($btc->error);
		}
		$r = $btc->sendtoaddress($vars->cleanp("address"), $vars->cleanp("amount"), "", "", false, $rpl);
		if (!$r) {
			$vars->finishAct($btc->error);
		}

		if ($vars->cleanp("save") == "yes") {
			$vars->book_send[$vars->cleanp("address")] = $vars->cleanp("comment");
			$vars->write();
		}
		$vars->finishAct();
	}

?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Loading...</title>

  <!-- Bootstrap core CSS -->
  <link href="assets/bootstrap.min.css" rel="stylesheet">
  <link href="assets/fa/css/font-awesome.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="#">
	<style>

		td i.fa-arrow-down {
			font-size: 18px;
			color: #03a929;
		}
		td i.fa-arrow-up {
			font-size: 18px;
			color: #a90329;
		}

    .hidden-on-pc {
		display: none;
/*		color: #5b93d3;*/
	}

	.container-new {
		padding-left: 15px;
		padding-right: 15px;
	}

	.container {
		max-width:100%;
	}

	@media only screen and (max-width: 760px),(min-device-width: 768px) and (max-device-width: 1024px)  {
	
	    .hidden-on-pc {
			display: inline;
/*			color: #5b93d3;
			margin-right: 10px;*/
		}

	    .hidden-on-mobile {
			display: none;
		}


		.table th, .table td {
		    border-top-color: transparent;
		}
		/* Force table to not be like tables anymore */
		table, thead, tbody, th, td, tr { 
			display: block; 
		}
		
		/* Hide table headers (but not display: none;, for accessibility) */
		thead tr { 
			position: absolute;
			top: -9999px;
			left: -9999px;
		}
		
		tr { border: 1px solid #ccc; }
		
		td { 
			/* Behave  like a "row" */
			border: none;
			border-bottom: 0px solid #eee; 
			position: relative;
			padding-left: 50%; 
		}
		
		td:before { 
			/* Now like a table header */
			position: absolute;
			/* Top/left values mimic padding */
			top: 6px;
			left: 6px;
			width: 45%; 
			padding-right: 10px; 
			white-space: nowrap;
		}
		
	}
	
	/* Smartphones (portrait and landscape) ----------- */
	@media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
		body { 
			padding: 0; 
			margin: 0; 
			}
		}
	
	/* iPads (portrait and landscape) ----------- */
	@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
		body { 
		}
	}

	h4 small {
		font-size: 12px;
		color: #aaa;
	}

	div.row h4 {
	 	margin-top: 20px;
	}

	.close .fa-close {
		font-size: 18px;
	}

	pre {
	    white-space: pre-wrap;       /* Since CSS 2.1 */
	    white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
	    white-space: -pre-wrap;      /* Opera 4-6 */
	    white-space: -o-pre-wrap;    /* Opera 7 */
	    word-wrap: break-word;       /* Internet Explorer 5.5+ */
	}
	
</style>

</head>

<body>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
    <div class="container">
      <span class="navbar-brand" href="javascript:;" id="">Balance: <span id="balance"><i class="fa fa-spin fa-spinner"></i></span> <sup><a id="a-refresh-balance" href="javascript:;" onclick="refreshBalance()" title="Click to refresh balance"><i class="fa fa-refresh"></i></a></sup> 
		<a href='javascript:;' style='color: #aaa; margin-left: 20px' data-action='chart' title='Click to view BTC on market'>
<!--
		<i class="fa fa-line-chart"></i>
-->
		1 BTC = <span id="btcrate"><i class="fa fa-spin fa-spinner"></i></span></a>
	  </span>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item active">
            <a class="nav-link" href="javascript:;" data-showpage="summary" data-title="Bitcoin Summary"><i class="fa fa-heartbeat"></i> Summary
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="javascript:;" data-showpage="history" data-title="Transactions History"><i class="fa fa-history"></i> History</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="javascript:;" data-popup="send"><i class="fa fa-arrow-up"></i> Send</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="javascript:;" data-popup="request"><i class="fa fa-arrow-down"></i> Request</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="javascript:;" data-showpage="book" data-title="Address Book"><i class="fa fa-address-book"></i>  Address Book</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="javascript:;" data-showpage="settings" data-title="Panel Settings"><i class='fa fa-cogs'></i> Settings</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Content -->
  <div class="container-new">
    <div class="row">
      <div class="col-lg-12" id="content">
      </div>
    </div>
  </div>

<div class="modal fade" id="div-chart" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 style="margin: auto; text-align: center">BTC on Market</h5>
               	<button data-dismiss="modal" class="close" type="button"><i class='fa fa-close'></i></button>
			</div>
			<div class="modal-body" style="">
				
			</div>
			<div class="modal-footer">
				<button class="btn btn-default" id="btn-chart-close">Close</button>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="div-request" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				Request BTC
               	<sup><button data-dismiss="modal" class="close" type="button"><i class='fa fa-close'></i></button></sup>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-12" style="margin-bottom: 10px">
						<label class="label">
							Address Type
						</label>
						<select id="request-type" class="form-control">
							<option value="legacy">legacy</option>
							<option value="p2sh-segwit" selected>p2sh-segwit (recommended)</option>
							<option value="bech32">bech32</option>
						</select>
					</div>
					<div class="col-lg-12" style="margin-top: 10px">
						<label class="label">
						<input type="checkbox" id="request-save" class="">
							Save to Address Book
						</label>
					</div>
					<div class="col-lg-12" data-hidden="yes">
						<label class="label">
							Comment
						</label>
						<input type="text" id="request-comment" class="form-control">
					</div>
					<div class="col-lg-12" data-hidden="yes" style="margin-top: 10px">
						<label class="label">
							New Address
						</label>
						<div class="input-group">
							<input type="text" readonly id="request-address" class="form-control">
							<div class="input-group-append">
								<button class="btn btn-sm btn-primary" id="request-copy"><i class='fa fa-copy'></i> Copy</button>
							</div>
						</div>
					</div>
					<div class="col-lg-12" style="text-align: center" data-hidden="yes">
						<img src="" id="request-qr">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary" id="btn-request-finish" data-show="yes">Get Address</button>
				<button class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="div-send" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				Send BTC
               	<button data-dismiss="modal" class="close" type="button"><i class='fa fa-close'></i></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-12" style="margin-bottom: 10px">
						<label class="label">
							Wallet Address
						</label>
						<input type="text" id="send-address" class="form-control">
					</div>
					<div class="col-lg-12">
						<label class="label">
						<input type="checkbox" id="send-save" class="">
							Save to Address Book
						</label>
					</div>
					<div class="col-lg-12" data-hidden="yes" style="margin-bottom: 10px">
						<label class="label">
							Comment
						</label>
						<input type="text" id="send-comment" class="form-control">
					</div>
				</div>
				<div class="row">
					<div class="col-lg-7">
						<label class="label">
							BTC Amount (<a href="javascript:;" data-action="use-max">Click to use max</a>)
						</label>
						<input type="text" id="send-btc" class="form-control">
					</div>
					<div class="col-lg-1">
						<div style="margin-top: 40px;">OR</div>
					</div>
					<div class="col-lg-4">
						<label class="label">
							USD Amount
						</label>
						<input type="text" id="send-usd" class="form-control">
					</div>
				</div>
				<div class="row" style="margin-top: 10px">
					<div class="col-lg-4">
						<label class="label">
							Priority
						</label>
						<select id="send-priority" class="form-control">
						</select>
					</div>
					<div class="col-lg-4">
						<label class="label">
							Fee (for 1Kb)
						</label>
						<input type="text" id="fee" class="form-control">
					</div>
					<div class="col-lg-4">
						<input type="text" id="feeusd" disabled class="form-control" style="margin-top: 32px">
					</div>
				</div>
				<div class="row" style="margin-top: 10px">
					<div class="col-lg-12">
						<label class="label">
						<input type="checkbox" id="send-check" class="">
						Don't check valid amount <sup><a href="javascript:;" title="This setting disables amount checking for Send amount and Fee amount"><i class='fa fa-info-circle'></i></a></sup>
						</label>
					</div>
					<div class="col-lg-12">
						<label class="label">
						<input type="checkbox" id="send-replace" class="">
						Make transaction replaceable <sup><a href="javascript:;" title="Allow this transaction to be replaced by a transaction with higher fees via BIP 125"><i class='fa fa-info-circle'></i></a></sup>
						</label>
					</div>
					<div class="col-lg-12">
						<label class="label">
							Your Wallet Password
						</label>
						<input id="send-password" class="form-control" type="password">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary" id="btn-send-finish" data-show="yes">Send Funds</button>
				<button class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>
<!--
<textarea>
<?php
//	for ($i=0; $i<50; $i++){
//		if ($address = $btc->getnewaddress("", "p2sh-segwit"))
//			print $address."\n";
//	}
?>
</textarea>
-->

<script>

	var rate = <?php print $rate?>;
	var refresh_interval = <?php print $vars->c["refresh_interval"]?>;

</script>

  <!-- Bootstrap core JavaScript -->
  <script src="assets/jquery.min.js"></script>
  <script src="assets/bootstrap.bundle.min.js"></script>
  <script src="assets/helper.js"></script>
  <script src="assets/notify.min.js"></script>
<!--
  <script src="https://widgets.bitcoin.com/widget.js"></script>
-->

</body>

</html>