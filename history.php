<?php
	include_once("config.php");

	$btc->check();

	$rate = $vars->getRate();

	prepareTables ("history", array("list"));

	$dirn = array ("receive" => "<a href='javascript:;' class='hidden-on-mobile' title='Received'><i class='fa fa-arrow-down'></i></a><span class='badge badge-success hidden-on-pc'>RECEIVED</span>", "send" => "<a href='javascript:;' class='hidden-on-mobile' title='Sent'><i class='fa fa-arrow-up'></i></a><span class='badge badge-danger hidden-on-pc'>SENT</span>");

	if ($vars->isAct("list")) {
		$j = array();
		$rows = $r = array();
		$transactions = $btc->listtransactions("*", 1000000);
		foreach ($transactions as $trans) {
			if ($trans["confirmations"]) {
				$conf = $trans["confirmations"] < 6 ? "<span class='hidden-on-pc'>Confirmations: </span>".$trans["confirmations"] : "<span class='badge badge-info'>CONFIRMED</span>";
				$r[] = array (array("id" => $trans["txid"]), $dirn[$trans["category"]], $trans["address"], sprintf("%01.8f", abs($trans["amount"]))."<span class='hidden-on-pc'> BTC</span>", "$".abs(round($trans["amount"]*$rate, 2)), $conf, $vars->date($trans["time"]), "<span class='hidden-on-pc'>TXID: </span><a href='https://blockchain.info/btc/tx/{$trans["txid"]}' title='Click to verify on blockchain.info' target='_blank'>".$trans["txid"]."</a>");
			}
		}

		$j["count"] = $total = count($r);
		$pages = ceil($total / $perpage["list"]);
		$j["pages"] = $pages;
		$offset = ($currentpage["list"] - 1) * $perpage["list"];
		$cnt = 0;
		$r = array_reverse($r);
		foreach ($r as $item) {
			if ($cnt < $offset) {
				$cnt++;
				continue;
			};
			if ($cnt >= ($offset + $perpage["list"])) {
				break;
			}
			$rows[] = $item;
			$cnt++;
		}
		$j["rows"] = $rows;
		$vars->finishAct($j);
	}

	if ($vars->act == ""):
?>
<style>
	table.table-sm thead {
		font-size: 12px;
	}
</style>

<div class="row">
	<div class="col-md-12">
				<h4>Transactions History <small>(sorted from newest to oldest)</small></h4>
				<table class="table table-sm" data-topic="list" data-paginator="yes" data-checkboxes="no">
					<thead class="thead-dark">
						<th style="width: 50px">Type</th>
						<th style="width: 350px">Address</th>
						<th style="width: 100px">BTC</th>
						<th style="width: 100px">USD <sup><a href='javascript:;' title='Values calculated with CURRENT exchange rate'><i class='fa fa-info-circle'></i></a></sup></th>
						<th style="width: 100px">Confirmations</th>
						<th style="width: 150px">Time</th>
						<th style="">TX</th>
					</thead>
					<tbody style="">
					</tbody>
				</table>
	</div>	
</div>	
<div class="row">
	<div class="col-md-12">
	</div>
</div>


<div class="modal fade" id="" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				Dialog
			</div>
			<div class="modal-body smart-form">
				<label class="textarea">
					<textarea class="custom-scroll" placeholder="" rows="10"></textarea>
				</label>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary" id="btn-id-finish">Finish</button>
				<button class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
	var fadeInterval = 500;

<?php
	print tableSettings (array("list", "metrics"));
?>	

	function pageloaded() {
		prepareTables ("history");
		wrapTimeout (function(){
			updateList ("history", "list", true, function(){
				$("#table-list a[title]").tooltip({trigger : 'hover'})
			});
		}, refresh_interval*1000);
	}

	$("#navbarResponsive li.active").removeClass("active");
	$("#navbarResponsive li a[data-showpage='history']").closest("li").addClass("active");

</script>
<?php
	endif;
?>