<?php
	include_once("config.php");

	prepareTables ("stats", array("list"));

	if ($vars->isAct("list")) {
		$table = "list";
		$j = array();
		$rows = array();
		$arr = $vars->read();
		foreach ($arr as $name => $val) {
			if (!preg_match("/^internal_/", $name)) {
				if ($name == "exchange_rate") {
					$rows[] = array (array("id" => $name), $name." <a href='javascript:;' data-hint='{$name}'><i class='fa fa-question-circle'></i></a>", "<select class='form-control' data-field='{$name}' data-value='$val'><option value='binance'>Binance</option><option value='bitfinex'>BitFinex</option><option value='bitstamp'>BitStamp</option></select>");
				} else {
					$rows[] = array (array("id" => $name), $name." <a href='javascript:;' data-hint='{$name}'><i class='fa fa-question-circle'></i></a>", "<input type=text class='form-control' data-field='{$name}' data-value='{$val}'>");
				}
			}
		}
		$j["rows"] = $rows;
		$vars->finishAct($j);
	}

	if ($vars->isAct("save")) {
		foreach ($vars->p["field"] as $name => $val) {
			$vars->c[$name] = $val;
		}
		$vars->write();
		$vars->finishAct(array("refresh_interval" => $vars->c["refresh_interval"]));
	}

	if ($vars->isAct("password")) {
		$opass = $vars->cleanp("opass");
		$npass = $vars->cleanp("npass");
		if (!$opass) {
			$vars->finishAct($btc->encryptwallet($npass) ? "" : $btc->error);
		} else {
			$vars->finishAct($btc->walletpassphrasechange($opass, $npass) ? "" : $btc->error);
		}
	}

	if ($vars->act == ""):
?>
<style>
	table.table-sm thead {
		font-size: 12px;
	}
</style>
<div class="row">
	<div class="col-md-12" style="text-align: center; margin: 20px">
		<button class="btn btn-danger" id="btn-password-change"> Change Wallet Password</button>
	</div>
</div>
<div class="row">
	<div class="col-md-3">
	</div>
	<div class="col-md-6">
		<table class="table table-hover" data-topic="list" data-paginator="no" data-checkboxes="no">
			<thead class="thead-dark">
				<th style="width: 120px">Name</th>
				<th style="width: 120px">Value</th>
			</thead>
			<tbody style="">
			</tbody>
		</table>
	</div>	
	<div class="col-md-3">
	</div>
	<div class="col-md-3">
	</div>
	<div class="col-md-6">
		<button class="btn btn-primary float-right" id="btn-save-settings">Save Settings</button>
	</div>
	<div class="col-md-3">
	</div>
</div>	


<div class="modal fade" id="div-password" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5>Change Wallet Password<br>
					<span class="text-danger" style="font-size: 12px; font-weight: bold">Warning! This operation is irreversible!</span>
				</h5>
               	<sup><button data-dismiss="modal" class="close" type="button"><i class='fa fa-close'></i></button></sup>
				
			</div>
			<div class="modal-body">
				<label class="label">
					Current Password <small>(leave blank if wallet is not encrypted)</small>
				</label>
				<input type="password" class="form-control" id="change-old">
				<label class="label">
					New Password
				</label>
				<input type="password" class="form-control" id="change-new">
				<label class="label">
					Re-type Password
				</label>
				<input type="password" class="form-control" id="change-new2">
				<label class="label">
					<input type="checkbox" id="password-show">
					Show passwords
				</label>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary" id="btn-password-finish">Change</button>
				<button class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<script>
	var fadeInterval = 500;

	var hints = {
				"btc_server_ip" : "Bitcoind remote RPC IP address",
				"btc_server_port" : "Bitcoind remote RPC port (default: 8332)",
				"btc_rpc_user" : "Bitcoind RPC user",
				"btc_rpc_password" : "Password for RPC user",
				"exchange_rate" : "Exchange rate server",
				"refresh_interval" : "Refresh interval for summary and balance (in seconds). Set 0 to disable auto refresh.",
				"log_portion" : "Entries number to get from tail of log file"
			}

<?php
	print tableSettings (array("list"));
?>	

	function pageloaded() {
		prepareTables ("settings");
		updateList ("settings", "list", true, function(){
			$("#table-list [data-value]").each(function(){
				$(this).val($(this).data("value"));
			});
			$("a[data-hint]").each(function(){
				if (hints[$(this).data("hint")]) {
					$(this).attr("title", hints[$(this).data("hint")])
				} else {
					$(this).remove();
				}
			}).tooltip();
		});

		bindClick ("#btn-save-settings", function(){
			var btn = $(this);
			var q = [];
			$("#table-list tbody *[data-field]").each(function(){
				q.push("field[" + $(this).data("field") + "]=" + encodeURIComponent($(this).val()));
			})
			btn.processing("show");
			$.ajax({
				type: "POST",
				url: "settings.php?act=save",
				data: q.join("&"),
				success: function(msg){
					if ((json = tryJSON(msg)) && (typeof json.refresh_interval !== "undefined")) {
						refresh_interval = json.refresh_interval;
						if (typeof tmBal !== "undefined") {
							clearTimeout(tmBal);
						}
						if (refresh_interval != 0) {
							wrapTimeoutB(function(){
								refreshBalance(false);
							}, refresh_interval*1000);
							$("#a-refresh-balance").hide();
						} else {
							$("#a-refresh-balance").show();
						}
	                }
					btn.processing("hide");
					showAlert("Settings saved", "success");
				}
			});
		})

		bindClick ("#btn-password-change", function(){
			$("#div-password input[type!='checkbox']").val("").prop("type", "password");
			$("#password-show").prop("checked", false);
			$("#div-password").modal("show");
		});

		bindClick ("#password-show", function(){
			$("#div-password input[type!='checkbox']").prop("type", $(this).is(":checked") ? "text" : "password");
		})

		bindClick ("#btn-password-finish", function(){
			var btn = $(this);
			btn.processing("show");
			var p = $("#change-old").val();
			var pn = $("#change-new").val();
			if (pn != $("#change-new2").val()) {
				showAlert("New passwords not match", "error");
			}
			$.ajax({
				type: "POST",
				url: "settings.php?act=password",
				data: "opass=" + encodeURIComponent(p) + "&npass=" + encodeURIComponent(pn),
				success: function(msg){
					btn.processing("hide");
					if (msg) {
						showAlert(msg, "error");
					} else {
						$("#div-password").modal("hide");
						showAlert("Password changed", "success");
					}
				}
			});
		})

	}

	$("#navbarResponsive li.active").removeClass("active");
	$("#navbarResponsive li a[data-showpage='settings']").closest("li").addClass("active");

</script>
<?php
	endif;
?>