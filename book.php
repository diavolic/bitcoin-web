<?php
	include_once("config.php");

	$btc->check();

	prepareTables ("stats", array("request", "send"));

	if ($vars->isAct("request")) {
		$table = "request";
		$j = array();
		$rows = array();

		$pages = ceil(count($vars->book_request) / $perpage[$table]);
		$j["pages"] = $pages;
		$j["count"] = $total = count($vars->book_request);
		$offset = ($currentpage[$table] - 1) * $perpage[$table];
		$cnt = 0;
		foreach ($vars->book_request as $address => $comment) {
			if ($cnt < $offset) {
				$cnt++;
				continue;
			};
			if ($cnt >= ($offset + $perpage[$table])) {
				break;
			}
			$rows[] = array (array("id" => $address), $address." <a href='javascript:;' title='Copy address to clipboard' data-copy='{$address}'><i class='fa fa-copy'></i></a>", $comment, "<a href=\"javascript:;\" data-action=\"delete-address\" data-type=\"request\" data-address=\"{$address}\" class=\"text-danger\" title=\"Delete address from addres book\"><i class='fa fa-close'></i></a>");
			$cnt++;
		}
		$j["rows"] = $rows;
		$vars->finishAct($j);
	}

	if ($vars->isAct("send")) {
		$table = "send";
		$j = array();
		$rows = array();

		$pages = ceil(count($vars->book_send) / $perpage[$table]);
		$j["pages"] = $pages;
		$j["count"] = $total = count($vars->book_send);
		$offset = ($currentpage[$table] - 1) * $perpage[$table];
		$cnt = 0;
		foreach ($vars->book_send as $address => $comment) {
			if ($cnt < $offset) {
				$cnt++;
				continue;
			};
			if ($cnt >= ($offset + $perpage[$table])) {
				break;
			}
			$rows[] = array (array("id" => $address), $address." <a href='javascript:;' title='Copy address to clipboard' data-copy='{$address}'><i class='fa fa-copy'></i></a>", $comment, "<a href=\"javascript:;\" data-action=\"delete-address\" data-type=\"request\" data-address=\"{$address}\" class=\"text-danger\" title=\"Delete address from addres book\"><i class='fa fa-close'></i></a>");
			$cnt++;
		}
		$j["rows"] = $rows;
		$vars->finishAct($j);
	}

	if ($vars->isAct("delete")) {
		switch ($vars->cleanp("type")) {
			case "send" : {
				unset($vars->book_send[$vars->cleanp("address")]);
				break;
			}
			case "request" : {
				unset($vars->book_request[$vars->cleanp("address")]);
				break;
			}
		}
		$vars->write();
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
	<div class="col-md-6">
		<div class="row">
			<div class="col-md-12">
				<h4>Request Addresses</h4>
				<table class="table table-sm" data-topic="request" data-paginator="yes" data-checkboxes="no">
					<thead class="thead-dark">
						<th style="width: 320px">Wallet Address</th>
						<th style="">Comment</th>
						<th style="width: 20px"></th>
					</thead>
					<tbody style="font-size: 12px">
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="row">
			<div class="col-md-12">
				<h4>Send Addreses</h4>
				<table class="table table-sm" data-topic="send" data-paginator="yes" data-checkboxes="no">
					<thead class="thead-dark">
						<th style="width: 320px">Wallet Address</th>
						<th style="">Comment</th>
						<th style="width: 20px"></th>
					</thead>
					<tbody style="font-size: 12px">
					</tbody>
				</table>
			</div>	
		</div>	
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
	print tableSettings (array("request", "send"));
?>	

	function pageloaded() {
		prepareTables ("book");
		updateList ("book", "request", true, function(){
				$("#table-request a[title]").tooltip({trigger : 'hover'})
			});
		updateList ("book", "send", true, function(){
				$("#table-send a[title]").tooltip({trigger : 'hover'})
			});
	}

	$("#navbarResponsive li.active").removeClass("active");
	$("#navbarResponsive li a[data-showpage='book']").closest("li").addClass("active");

</script>
<?php
	endif;
?>