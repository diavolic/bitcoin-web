<?php
	include_once("config.php");

	$btc->check();

	$rate = $vars->getRate();

	prepareTables ("summary", array("list", "metrics"));

	$dirn = array ("receive" => "<a href='javascript:;' title='Received'><i class='fa fa-arrow-down'></i></a>", "send" => "<a href='javascript:;' title='Sent'><i class='fa fa-arrow-up'></i></a>");

	if ($vars->isAct("list")) {
		$j = array();
		$rows = array();
		$transactions = $btc->listtransactions("*");
		foreach ($transactions as $trans) {
			if ($trans["confirmations"] == 0) {
				$rows[] = array (array("id" => $trans["txid"]), $dirn[$trans["category"]], $trans["address"], sprintf("%01.8f", abs($trans["amount"])), "$".round(abs($trans["amount"]*$rate), 2), $vars->date($trans["time"]), "<a href='https://blockchain.info/btc/tx/{$trans["txid"]}' title='Click to verify on blockchain.info' target='_blank'>".shortenString($trans["txid"], 26)."</a>");
			}
		}
		$j["rows"] = $rows;
		$vars->finishAct($j);
	}


	if ($vars->isAct("console")) {
		$cmd = $vars->cleanp("cmd");
		$params = str_getcsv($cmd, " ");
//		$func = array_shift($params);
		$out = call_user_func_array(array($btc, "__call"), $params);
/*
		switch ($func) {
			case "help" : {
				$out = $btc->help();
				break;
			}
		}
*/
		$vars->finishAct($out);
	}

	if ($vars->isAct("metrics")) {
		$r = array();
		if ($j = $btc->getblockchaininfo()) {
			if (is_array($j)) {
				$r["rows"][] = array("", "BTC network block", $j["headers"]);
				$r["rows"][] = array("", "Local BTC block", ($j["blocks"] == $j["headers"]) ? $j["blocks"] : " {$j["blocks"]} <label class='badge badge-danger'>INCONSISTENT!!!</label> <a href='javascript:;' title='This means your bitcoin daemon is not synchronized with global bitcoin network. Check bitcoind status and Internet rich, then wait till blocks synchronizing.'><i class='fa fa-info-circle'></i></a>");
				$r["rows"][] = array("", "BTC daemon path", $btc->getPath());
				$r["rows"][] = array("", "Disk occupied size", "<span data-bytes='".dirSize($btc->getPath())."'></span>");
				$r["rows"][] = array("", "Free disk size", "<span data-bytes='".disk_free_space("/")."'></span>");
			} else {
				$r["rows"][] = "Information inaccessible, bitcoind returns: ".$j;
			}
		} else {
			$r["rows"][] = "Information inaccessible, possible error: ".$btc->error;
		}
		$vars->finishAct($r);
	}

	if ($vars->isAct("debug.log")) {
//		$vars->finishAct($btc->getPath()."/debug.log");
		$vars->finishAct(tail($btc->getPath()."/debug.log", $vars->c["log_portion"]));
	}

	if ($vars->isAct("backup-check")) {
		$_SESSION["checked"] = false;
		$btc->walletpassphrase($vars->cleanp("pass"), 30);
		if ($btc->error) {
			$vars->finishAct($btc->error);
		}
		$_SESSION["checked"] = true;
		$_SESSION["suser"] = $vars->p["user"];
		$_SESSION["supass"] = $vars->p["upass"];
	}

	if ($vars->isAct("backup") && ($_SESSION["checked"])) {
		$_SESSION["checked"] = false;
//		$temp_name = tempnam(sys_get_temp_dir(), 'backup_');
//		if (!$btc->backupwallet($temp_name)) {
//			$vars->finishAct(array($btc->error));
//		}

		$wdir = dirname(__FILE__)."/writable";
		if (!$btc->backupwallet($wdir)) {
			error_log($btc->error);
		}
		$uniq = uniqid();
		$cuser = exec("whoami");
error_log("echo {$_SESSION["supass"]} | sudo -u {$_SESSION["suser"]} -S chmod 644 ".$wdir."/".$uniq);
		exec("echo {$_SESSION["supass"]} | sudo -u {$_SESSION["suser"]} -S chmod 644 ".$wdir."/".$uniq);
		rename ($wdir."/wallet.dat", $wdir."/".$uniq);
	    header('Content-Type: application/octet-stream');
	    header('Content-Disposition: attachment; filename="wallet__'.gmdate("d_M_Y__H_i").'.bak"');
	    header('Content-Transfer-Encoding: binary');
	    header('Accept-Ranges: bytes');			
//	    header('Content-Length: '.filesize($temp_name));
		$f = fopen ($wdir."/".$uniq, "rb");
		fpassthru($f);
		fclose ($f);
		$vars->finishAct();
	}

	if ($vars->act == ""):
?>
<style>
	table.table-sm thead {
		font-size: 12px;
	}
</style>

<div class="row">
	<div class="col-md-4">
		<div class="row">
			<div class="col-md-12">
				<h4>System Information</h4>
				<table class="table table-sm" data-topic="metrics" data-paginator="no" data-checkboxes="no">
					<thead class="thead-dark">
						<th style="">Metrics</th>
						<th style="width: 200px">Value</th>
					</thead>
					<tbody style="">
					</tbody>
				</table>
			</div>
			<div class="col-md-12" style="">
				<button class="btn btn-primary" id="btn-console"> RPC Console</button>
				<button class="btn btn-primary float-right" id="btn-wallet-backup"> Backup Wallet</button>
			</div>
		</div>
	</div>
	<div class="col-md-8">
		<div class="row">
			<div class="col-md-12">
				<h4>Unconfirmed transactions</h4>
				<table class="table table-sm" data-topic="list" data-paginator="no" data-checkboxes="no">
					<thead class="thead-dark">
						<th style="width: 50px">Type</th>
						<th style="width: 350px">Address</th>
						<th style="width: 100px">BTC</th>
						<th style="width: 100px">USD</th>
						<th style="width: 150px">Time</th>
						<th style="">TX</th>
					</thead>
					<tbody style="">
					</tbody>
				</table>
			</div>	
		</div>	
	</div>	
	<div class="col-md-12">
		<h4>Last <?php print $vars->c["log_portion"]?> lines from daemon log file</h4>
		<pre style='height: 400px; color:#fff; background-color: #000' id='daemon-log'></pre>
	</div>
</div>	
<div class="row">
	<div class="col-md-12">
	</div>
</div>


<div class="modal fade" id="div-console" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				RPC Console (bitcoin-cli wrapper)
			</div>
			<div class="modal-body smart-form">
				<pre style="height: 400px; color: #fff; background-color: #000" id="rpc-output"></pre>
				<div class="row">
					<div class="col">
						<input type="text" class="form-control" id="rpc-command" placeholder="Type 'help' if forget commands">
					</div>
					<div class="col" style="max-width: 150px; margin-top: 4px">
						<button class="btn btn-primary btn-sm" id="btn-rpc-send">SEND</button>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="div-password" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5>Backup Wallet<br>
				</h5>
               	<sup><button data-dismiss="modal" class="close" type="button"><i class='fa fa-close'></i></button></sup>
				
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-12" style="">
				<label class="label">
					Wallet password <small>(leave blank if wallet is not encrypted)</small>
				</label>
				<input type="password" class="form-control" id="password">
				<label class="label">
					<input type="checkbox" id="password-show">
					Show password
				</label>
					</div>
					<div class="col-lg-6" style="">
				<label class="label">
					User with "sudo" permission
				</label>
				<input type="text" class="form-control" id="sudo-user">
					</div>
					<div class="col-lg-6" style="">
				<label class="label">
					User password
				</label>
				<input type="password" class="form-control" id="sudo-password">
					</div>
					</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary" id="btn-password-finish">Backup</button>
				<button class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>


<script>
	var fadeInterval = 500;

<?php
	print tableSettings (array("list", "metrics"));
?>	

	function updateLogs() {
		$.ajax({
			type: "GET",
			url: "summary.php?act=debug.log",
			success: function(msg){
				$("#daemon-log").html(msg).scrollTop($("#daemon-log").prop("scrollHeight"));
			}
		})
	}

	function pageloaded() {
		prepareTables ("summary");

		wrapTimeout (function(){
			updateList ("summary", "list", true, function(){
				$("#table-list a[title]").tooltip({trigger : 'hover'})
			});
			updateList ("summary", "metrics", false, function(){
				$("#table-metrics a[title]").tooltip({trigger : 'hover'})
				$("#table-metrics [data-bytes]").each(function(){
					$(this).text(formatBytes($(this).data("bytes")));
				})
			});
			updateLogs();
		}, refresh_interval*1000);


		bindClick ("#btn-wallet-backup", function(){
			$("#div-password #password").val("").prop("type", "password");
			$("#password-show").prop("checked", false);
			$("#div-password").modal("show");
		});

		bindClick ("#password-show", function(){
			$("#div-password #password").prop("type", $(this).is(":checked") ? "text" : "password");
		})

		bindClick ("#btn-password-finish", function(){
			var btn = $(this);
			btn.processing("show");
			var p = $("#password").val();
			var u = $("#sudo-user").val();
			var pu = $("#sudo-password").val();
			$.ajax({
				type: "POST",
				url: "summary.php?act=backup-check",
				data: "pass=" + encodeURIComponent(p) + "&user=" + encodeURIComponent(u) + "&upass=" + encodeURIComponent(pu),
				success: function(msg){
					btn.processing("hide");
					if (msg) {
						showAlert(msg, "error");
					} else {
						$("#div-password").modal("hide");
						window.location.href = "summary.php?act=backup";
					}
				}
			});
		})
		
		bindClick ("#btn-rpc-send", function(){
			var btn = $(this);
			btn.processing("show");
			var cmd = $("#rpc-command").val();
			$("#rpc-command").val("");
			$.ajax({
				type: "POST",
				url: "summary.php?act=console",
				data: "cmd=" + encodeURIComponent(cmd),
				success: function(msg){
					btn.processing("hide");
					if (json = tryJSON (msg)) {
						msg = JSON.stringify(json, null, 2);
					}
					$("#rpc-output").html(msg).scrollTop($("#rpc-output").prop("scrollHeight"));
				}
			});
		})

		$("#div-console input").keypress(function(e){
			if (e.which==13)
				$("#btn-rpc-send").trigger("click");
		})
		
		bindClick("#btn-console", function(){
			$("#div-console input").val("");
			$("#div-console pre").html("");
			$("#div-console").modal("show");
		})

	}

	$("#navbarResponsive li.active").removeClass("active");
	$("#navbarResponsive li a[data-showpage='summary']").closest("li").addClass("active");

</script>
<?php
	endif;
?>