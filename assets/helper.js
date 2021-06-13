	var current_page = window.location.hash.substr(1).replace("#", "");
	var tmp = window.location.href.match(/\?[^#]+/);

	var currency = 0; // btc - 0, usd - 1
	var btcBalance = 0;
	var btcFee = {};

	var admin_title = "Bitcoin Panel";
	var page_title = "";

	if (!tmp) {
		current_params = "";
	} else {
		current_params = tmp[0];
	}

	isMobile = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4));    

	function formatBytes(bytes, decimals = 2) {
		if (isNaN(bytes)) {
			return "Invalid value: " + bytes;
		}
	    if (bytes === 0) return '0 Bytes';
	    const k = 1024;
	    const dm = decimals < 0 ? 0 : decimals;
	    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	    const i = Math.floor(Math.log(bytes) / Math.log(k));
	    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
	}

	function tryJSON (str, c = false) {
			var json;
			if (str) 
			{
			    try {
		    	    json = JSON.parse(str);
			    } catch(e) {
					if (c) {
						console.log(e);
						console.log(str);
					}
					return false;
			    }
				return json;
			} else
				return false
	}



	function pagination(currentPage, nrOfPages) {
	    var delta = 2,
	        range = [],
	        rangeWithDots = [],
	        l;

	    range.push(1);  

	    if (nrOfPages <= 1){
		 	return range;
	    }

	    for (let i = (currentPage - delta); i <= (1*currentPage + 1*delta); i++) {
	        if (i < nrOfPages && i > 1) {
	            range.push(i); 
	        }
	    }  
	    range.push(nrOfPages);

	    for (let i of range) {
	        if (l) {
	            if (i - l === 2) {
	                rangeWithDots.push(l + 1);
	            } else if (i - l !== 1) {
	                rangeWithDots.push('...');
	            }
	        }
	        rangeWithDots.push(i);
	        l = i;
    	}
	    return rangeWithDots;
	};

// cal to place pagination buttons to element 'elem' with current page 'c' and total pages 'm'
	function pageHTML(elem, c, m) {
		var html = "";
		var pages = pagination (c, m);
		for (i=0; i<pages.length; i++)
		{
			if (pages[i] != "...")
			{
				if ((pages[i] ==c) || (c == 0))
					html = html + '<div style="display: table-cell"><button disabled class="btn btn-fill btn-primary btn-xs" current-page="' + pages[i] + '" >' + pages[i] + '</button></div>';
				else
					html = html + '<div style="display: table-cell"><button class="btn btn-link btn-xs btn-page" current-page="' + pages[i] + '" title="Move to page #' + pages[i] + '">' + pages[i] + '</button></div>';		
			} else
			{
				html = html + '<div style="display: table-cell"><span style="padding-left: 5px; padding-right: 5px">...</span></div>';
			}
		}
		$(elem).html(html);
		return html;
	}

	function showpage(link, funcsuccess, funcerror) {
			$("#page-title").html("<i class='fa fa-spin fa-spinner'></i> Loading page...");
			$("title").text("Loading...");
			pageloaded = null;
			if (current_params) {
				$("#main").css("margin-left", "0px");
			}
			$.ajax({
				type: "GET",
				url: link + current_params + window.location.hash,
				success: function(msg){
					wrapTimeout();
					wrapTimeoutB();
					if (refresh_interval != 0) {
						wrapTimeoutB(function(){
							refreshBalance(false);
						}, refresh_interval*1000);
						$("#a-refresh-balance").hide();
					} else {
						$("#a-refresh-balance").show();
					}
					$("#content").fadeOut(500, function(){
						current_page = link.replace('.php', '');
						current_page = current_page.replace(/\?.+/g, '');
						window.location.href = window.location.href.replace(/#+.*/, "") + "#" + current_page;
						$("#content").html(msg).tooltip();
						$("#content").fadeIn(1000, function(){
						if ($.isFunction(funcsuccess))
							funcsuccess();
						if ($.isFunction(pageloaded))
							pageloaded();
						});
						var a = $("#navbarResponsive li.active a.nav-link");
						page_title = a.data("title");
						if (!page_title) {
							page_title = a.text();
						}
						$("#page-title").html(page_title);
						$("title").text(page_title + " - " + admin_title);
						$("#content *.tooltip").remove();
						$("a[title]").tooltip({trigger: "hover"});
					});
				},
				error: function(msg){
					if (typeof tmBal !== "undefined") {
						clearTimeout(tmBal);
					}
					if (typeof tmId !== "undefined") {
						clearTimeout(tmId);
					}
					$("#content").html("Error when opening " + link + ": " + msg).fadeIn(1000);
					if ($.isFunction(funcerror))
						funcerror();
				}
			});
			return false;
		}

		var event2function = {
					"click" : "bindClick", 
					"dblclick" : "bindDblClick", 
					"change" : "bindChange", 
					"keypress" : "bindKeyPress", 
					"keyup" : "bindKeyUp", 
					"keydown" : "bindKeyDown",
					"mouseenter" : "bindMouseEnter", 
					"mouseleave" : "bindMouseLeave", 
		};
		for (i in event2function) {
			var f = "var " + event2function[i] + " = function (elem, func) { $(document).off('" + i + "', elem).on('" + i + "', elem, func); }";
			eval(f);
		}

		$(document).ready (function(){

			$.fn.extend({
				processing: function (state = "show") {
					if (($(this).prop("nodeName") == "BUTTON") || ($(this).prop("nodeName") == "A"))
					{
						$(this).find("i.fa-spinner").remove();
						if (state == "show")
							$(this).html("<i class='fa fa-spinner fa-spin'></i> " + $(this).html());
						$(this).prop("disabled", state == "show");
						return this;
					};
					if ($(this).prop("nodeName") == "TD")
					{
						if (state == "show")
						{
							$(this).html("<i class='fa fa-spinner fa-spin'></i>" + $(this).html());
							$(this).addClass("processing");
						} else
						{
							$(this).removeClass("processing");
							$(this).find("i.fa-spinner:first").remove();
						}
						return this;
					}
				}
			})

			bindClick("a[data-showpage]", function(){
				var a = $(this);
				showpage(a.data('showpage') + '.php', function(){a.closest("nav").find("li").each(function(){$(this).removeClass("active")}); a.closest("li").addClass("active")});
			})
			


			if (!current_page)
				$("a[data-showpage]:first").trigger("click");
			else
				showpage(current_page + '.php');

			bindClick(".btn-page", function(){
				var topic = $(this).closest("div[data-topic]").data("topic");
				var page = $(this).closest("div[data-topic]").data("page");
				currentpage[topic] = $(this).attr("current-page");
				updateList(page, topic);
			})

			bindChange (".table-per-page", function(){
				var topic = $(this).data("topic");
				var page = $(this).data("page");
				currentpage[topic] = 1;
				perpage[topic] = $(this).val();
				updateList(page, topic);
			})

			bindClick("input.row-check-all", function () {
				var checked = $(this).is(":checked");
				var mark_checked = $(this).is(":checked") ? "YES" : "";
				$(this).closest("table").find("tbody input.row-check").prop("checked", checked);
				$(this).closest("table").find("tbody tr").attr("data-checked", mark_checked);
			})

			bindClick("input.row-check", function () {
				var checked = $(this).is(":checked") ? "YES" : "";
				$(this).closest("tr").attr("data-checked", checked);
			})

			bindClick ("button.btn-prompt", function() {
				eval("var func = " + $(this).data("function"));
				if (typeof func === "function") {
					func();
				}
				$(this).closest("div.modal").modal("hide");
			})

			bindClick (".sort-selector", function(){
				var table = $(this).closest("table");
				var page = table.data("page");
				var topic = table.data("topic");
				var direction = $(this).data("direction");
				if (!direction) {
					direction = 1;
				} else {
					direction = direction > 0 ? -1 : 1;
				}
				var icon = (parseInt(direction) > 0) ? "up" : "down";
				sortcolumn[topic]["field"] = $(this).data("field");
				sortcolumn[topic]["direction"] = direction;
				$(this).data("direction", direction);
				table.find("a.sort-selector i").removeClass("fa-sort").removeClass("fa-sort-up").removeClass("fa-sort-down").addClass("fa-sort");
				$(this).find("i").removeClass("fa-sort").addClass("fa-sort-" + icon);
				updateList (page, topic);
			})
			
			bindClick ("#btn-chart-close", function(){
				$("#div-chart .modal-body").html("");
				$("#div-chart").modal("hide");
			});

			bindClick ("a[data-action]", function(){
				switch ($(this).data("action")) {
					case "currency": {
						currency = 1 - currency;
						var spt = $("#balance span:first");
						var sut = $("#balance sub span");
						var t = spt.text();
						spt.text(sut.text());
						sut.text(t);
						break;
					}
					case "chart" : {
//						$("#div-chart .modal-body").html('<div class="btcwdgt-chart" bw-theme="light" style=""></div>');
						$("#div-chart .modal-header h5").text("Current exchange: 1 BTC = $ " + rate.toFixed(2));
						$("#div-chart .modal-body").html('<div style="height:560px; background-color: #FFFFFF; overflow:hidden; box-sizing: border-box; border: 1px solid #56667F; border-radius: 4px; text-align: right; line-height:14px; font-size: 12px; font-feature-settings: normal; text-size-adjust: 100%; box-shadow: inset 0 -20px 0 0 #56667F;padding:1px;padding: 0px; margin: 0px; width: 100%;"><div style="height:540px; padding:0px; margin:0px; width: 100%;"><iframe src="https://widget.coinlib.io/widget?type=chart&theme=light&coin_id=859&pref_coin_id=1505" width="100%" height="536px" scrolling="auto" marginwidth="0" marginheight="0" frameborder="0" border="0" style="border:0;margin:0;padding:0;line-height:14px;"></iframe></div></div>');
//						$("#btcwdgt").remove();
//						$("body").append("<script id='btcwdgt' src='https://widgets.bitcoin.com/widget.js'>");
						$("#div-chart").modal("show");
						break;
					}
					case "use-max" : {
						var fee = $("#fee").val();
						if (!isNaN (fee)) {
							var i = (btcBalance - fee).toFixed(8);
							if (i<0) {
								i = 0;
								showAlert("Not enough funds to pay fee", "error");
							}
							$("#send-btc").val(i).trigger("keyup");
						}
						break;
					}
					case "delete-address" : {
						var tr = $(this).closest("tr");
						var type = $(this).data("type");
						var address = $(this).data("address");
						tr.css("opacity", "0.5");
						$.ajax({
							type: "POST",
							url: "book.php?act=delete",
							data: "type=" + type + "&address=" + encodeURIComponent(address),
							success: function(msg){
								tr.css("opacity", "");
								if (msg) {
									showAlert(msg, "error");
								} else {
									updateList ("book", type, true);
								}
							}
						});
						break;
					}
				}
			})



		
			bindClick ("a[data-popup]", function(){
				switch ($(this).data("popup"))	 {
					case "request" : {
						var div = $("div#div-request");
						div.find("input").val("").prop("disabled", false);
						div.find("select").prop("disabled", false);
						div.find("[data-hidden='yes']").hide();
						div.find("[data-show='yes']").show();
						$("#request-save").prop("checked", false);
						div.modal("show");
						break;
					}
					case "send" : {
						var div = $("div#div-send");
						var sel = div.find("#send-priority");
						div.find("input").val("");
						div.find("#feeusd").prop("disabled", true);
						div.find("[data-hidden='yes']").hide();
						div.find("[data-show='yes']").show();
						sel.html('<option value="' + btcFee.eco + '" >Econom</option><option value="' + btcFee.low + '" selected>Regular</option><option value="' + btcFee.high + '">Fast</option>').trigger("change");
						$("#send-save").prop("checked", false);
						$("#send-check").prop("checked", false);
						$("#send-replace").prop("checked", true);
						div.modal("show");
						break;
					}
				}
			})
		
			bindChange("#send-priority", function(){
				$("#fee").val($(this).val()).trigger("keyup");
			});

			bindKeyUp("#fee", function(){
				var fee = $(this).val();
				if (!isNaN(fee) && (fee !='')) {
					$("#feeusd").val("$ " + (fee*rate).toFixed(2)).removeClass("is-invalid");
				} else {
					$("#feeusd").val("$ --.--").addClass("is-invalid");
				}
				checkAmount();
			})
	
			bindKeyUp("#send-usd", function(){
				var amount = $(this).val();
				if (!isNaN(amount) && (amount !=''))
					$("#send-btc").val ((amount/rate).toFixed(8));
				else
					$("#send-btc").val("0");
				checkAmount();
			})
	
			bindKeyUp("#send-btc", function(){
				var amount = $(this).val();
				if (!isNaN(amount) && (amount !=''))
					$("#send-usd").val ((amount*rate).toFixed(2));
				else
					$("#send-usd").val("0");
				checkAmount();
			})

			bindChange("#request-save", function(){
				if ($(this).is(":checked")) {
					$("#request-comment").closest("div").show();
				} else {
					$("#request-comment").closest("div").hide();
				}
			})

			bindChange("#send-save", function(){
				if ($(this).is(":checked")) {
					$("#send-comment").closest("div").show();
				} else {
					$("#send-comment").closest("div").hide();
				}
			})

			bindClick("#btn-request-finish", function(){
				var btn = $(this);
				var save = $("#request-save").is(":checked") ? "yes" : "";
				btn.processing("show");
				$.ajax({
					type: "POST",
					url: "index.php?act=request",
					data: "type=" + $("#request-type").val() + "&save=" + save + "&comment=" + encodeURIComponent($("#request-comment").val()),
					success: function(msg){
						btn.processing("hide");
						if (json = tryJSON(msg)) {
							$("#request-address").val(json.address).closest("div[data-hidden]").show();
							$("#request-qr").prop("src", json.qr).closest("div[data-hidden]").show();
							$("#request-type,#request-comment,#request-save").prop("disabled", true);
							btn.hide();
							if (save == "yes") {
								updateList ("book", "request", true);
							}
						} else {
							showAlert(msg, "error");
						}                   
					}
				})
			})

			bindClick("#btn-send-finish", function(){
				if (isNaN($("#send-btc").val()) || ($("#send-btc").val() == 0) || $("#send-btc").hasClass("is-invalid")) {
					showAlert("Invalid BTC amount", "error");
					return false;
				}
				if (!$("#send-address").val().trim()) {
					showAlert("Empty receiving wallet", "error");
					return false;
				}
				var btn = $(this);
				var save = $("#send-save").is(":checked") ? "yes" : "";
				var rpl = $("#send-replace").is(":checked") ? "yes" : "";
				btn.processing("show");
				$.ajax({
					type: "POST",
					url: "index.php?act=send",
					data: "address=" + $("#send-address").val() + "&save=" + save + "&comment=" + encodeURIComponent($("#send-comment").val()) + "&amount=" + $("#send-btc").val() + "&replace=" + rpl + "&fee=" + $("#fee").val() + "&password=" + encodeURIComponent($("#send-password").val()) ,
					success: function(msg){
						btn.processing("hide");
						if (!msg) {
							updateList ("summary", "list", true);
							showAlert("BTC have been sent");
							if (save == "yes") {
								updateList ("book", "send", true);
							}
							$("#div-send").modal("hide");
						} else {
							showAlert(msg, "error");
						}                   
					}
				})
			})

			bindClick("#request-copy", function(){
				$("#request-address").select();
				if (document.execCommand("copy")) {
					showAlert("New wallet address copied to clipboard");
				}
			})
	
			bindClick("a[data-copy]", function(){
				$("body").append("<input type='text' style='' id='tmp-copy'>");
				$("#tmp-copy").val($(this).data("copy")).select();
				if (document.execCommand("copy")) {
					showAlert("Information copied to clipboard");
				}
				$("#tmp-copy").remove();
			})

			bindChange("#send-check", function(){
				if ($(this).is(":checked")) {
					$("#send-btc").removeClass("is-invalid");
				} else {
					checkAmount();
				}
			})

			refreshBalance();
// ------------------ end of document.ready			
		})

		function refreshBalance(dec = true){
			if (dec) {
				$("#balance").html('<i class="fa fa-spin fa-spinner"></i>');
			}
			$.ajax({
				type: "GET",
				url: "index.php?act=balance",
				success: function (msg){
					if (json = tryJSON(msg)) {
						rate = json.rate;
						btcBalance = json.balance;
						btcFee = json.fee;
						var usd = json.balance*rate;
						if (currency) {
							b1 = "$" + usd.toFixed(2);
							b2 = json.balance + " BTC";
						} else {
							b1 = json.balance + " BTC";
							b2 = "$" + usd.toFixed(2);
						}
						var html = "<span>" + b1 + "</span>";
						html = html + " <sub><a href='javascript:;' title='Click to change primary currency' style='color: #fff' data-action='currency'><i class='fa fa-exchange'></i></a> <span style='color: #aaa'>"  + b2 + "</span></sub>";
						$("#balance").html(html);
						$("#btcrate").html("$ " + rate);
						$("a[data-action='currency']").tooltip({trigger: "hover"});
						if ((json.new !== "undefined") && json.new) {
//							new Audio("assets/income.mp3").play();
						}
					} else {
						showAlert (msg, "error");
					}
				}
			})
		}

		function showAlert (msg, cls="success") {
			if (cls == "danger")
				cls = "error";
			if (cls == "error") {
				console.log("SITE ERROR: " + msg);
			}
			$.notify(
  				msg, 
			  { position: isMobile ? "top left" : "top center", className: cls, autoHideDelay: 5000 }
			);
		}


	function setFilters (topic, f) {//  f example: {"id": 56, "name" : "myname", ....., "param": "value"}		var sd = sc = "";
		if (filters[topic] !== "undefined") {
			for (i in f) {
				filters[topic][i] = f[i];
			}
			currentpage[topic] = 1;
		}
	}

	function updateRow (page, topic, id) {
		var table = $("#table-" + topic);
		if (table.length && id) {
			var tr = table.find("tr[data-id='" + id + "']");
			if (!tr.length) {
				return false;
			}
			var checkboxes = (typeof table.data("checkboxes") !== "undefined") && (table.data("checkboxes").toUpperCase() == "YES");
			var cols = table.find("thead th").length;
			var store = tr.html();
			tr.html("<td colspan='" + cols + "' class='text-align: center'><i class='fa fa-spin fa-spinner'></i> Updating row...</td>"); 
			$.ajax({
				type: "POST", 
				url: page + ".php?act=" + topic + "-row",
				data: "id=" + id,
				success: function(msg){
					tr.html(store);
					if (json = tryJSON(msg)) {
						var html = "";
						if (checkboxes) {
							html += "<td class='smart-form'><label class='checkbox'><input type='checkbox' class='row-check'><i></i></label></td>";
						}
						for (i in json) {
							html = html + "<td>" + json[i] + "</td>";
						}
						tr.html(html);
					} else {
						showAlert (msg, "error");
					}
				}
			})
		}
	}

	function updateList (page, topic, decoration = false, callback = null) {
		if (page != current_page) {
			return false;
		}
		var tbl = $("#table-" + topic);
		if (!tbl.length) { 
			return false;
		}
		var cols = tbl.find("thead th").length;
		var arrFilters = [];
		var cp = (typeof currentpage !== "undefined") && (typeof currentpage[topic] !== "undefined") ? currentpage[topic] : "";
		var pp = (typeof perpage !== "undefined") && (typeof perpage[topic] !== "undefined") ? perpage[topic] : "";
		var fls = (typeof filters !=="undefined") && (typeof filters[topic] !== "undefined") ? filters[topic] : [];
		if (tbl.data("decoration") == "YES") {
			decoration = true;
		}
		var sc = sd = "";
			 // filters["name"] = "value"; filters["example"] = "value1"
		if ( (typeof sortcolumn !=="undefined") && (typeof sortcolumn[topic] !== "undefined"))  {
			sc = typeof sortcolumn[topic]["field"] !== "undefined" ? sortcolumn[topic]["field"] : "";
			sd = typeof sortcolumn[topic]["direction"] !== "undefined" ? sortcolumn[topic]["direction"] : "";
		}
		if (tbl.length) {
			var checkboxes = (typeof tbl.data("checkboxes") !== "undefined") && (tbl.data("checkboxes").toUpperCase() == "YES");
			for (i in fls) {
				arrFilters.push(i + "=" + encodeURIComponent(fls[i]));
			}
			tbl.find("tbody").fadeOut(decoration ? fadeInterval : 0, function(){
				var tbody = $(this);
				if (decoration) {
					tbody.html("<tr><td colspan='" + tbl.find("thead th").length+ "'><i class='fa fa-spinner fa-spin'></i> Updating table..</td></tr>");
					tbl.data("decoration", "YES");
				}
				tbody.fadeIn(decoration ? fadeInterval : 0, function(){
					$.ajax({
						type: "POST",
						url: page + ".php?act=" + topic,
						data: "currentpage=" + cp + "&perpage=" + pp + "&sortcolumn="  + sc + "&sortdirection="  + sd + "&" + arrFilters.join("&"),
						success: function(msg) {
							if (json = tryJSON (msg, true)) {
								var pages = typeof json.pages !== "undefined" ? json.pages : false;
								if (typeof json.debug !== "undefined") {
									console.log(json.debug);
								}
								var html = "";
								if (json.rows.length > 0) {
									for (i in json.rows) {
										var first = true;
										for (j in json.rows[i]) {
											if (first) {
												var trProp = {};
												var tmp = json.rows[i][j];
												if (typeof tmp === "object") {
													trProp = tmp;
												} else {
													if (parseInt(tmp)) {
														trProp["id"] = parseInt(tmp);
													}
												}
												var str = [];
												for (h in trProp) {
													str.push("data-" + h + "='" + trProp[h] + "'")
												}
												html += "<tr " + str.join(" ") + ">";
												if (checkboxes) {
													if (typeof trProp.id !== "undefined") {
														html += "<td class='smart-form'><label class='checkbox'><input type='checkbox' class='row-check'><i></i></label></td>";
													} else {
														html += "<td>&nbsp;</td>";
													}
												}
												first = false;
												continue;
											}
											html += "<td>" + json.rows[i][j] + "</td>";
										}
										html += "</tr>";
									}
									if (checkboxes) {
										tbl.find("input.row-check-all").prop("checked", false);
									}
								} else {
									html = "<tr><td colspan='" + cols + "'>No data found</td></tr>"
								}
								if (json.header) {
									tbl.closest("div.row").find(".header").html(json.header);
								}
								tbody.fadeOut(decoration ? fadeInterval : 0, function(){
									tbody.html(html).fadeIn(decoration ? fadeInterval : 0, function() {
										if (pages) {
											pageHTML("#" + topic + "-paginator", cp, pages);
										} else {
											if (typeof currentpage !== "undefined") {
												currentpage[topic] = 1;
											}
											$("#" + topic + "-paginator").html('');
										}
										if (typeof callback === "function") {
											callback();
										}
									});
								});
							} else {
								showAlert (msg, "error");
							}
						}
					})
				});


			});
		}
	}

	function promptEx (caption, text, funcYes, funcNo = null) {
		$("body #div-prompt").remove();
		$("body").append('<div class="modal fade" id="div-prompt" data-keyboard="false" data-backdrop="static">\
							<div class="modal-dialog">\
								<div class="modal-content">\
									<div class="modal-header">' + caption + '\
									</div>\
									<div class="modal-body">' + text +' \
									</div>\
									<div class="modal-footer">\
										<button class="btn btn-primary btn-prompt" data-function="' +  funcYes + '">YES</button>\
										<button class="btn btn-default btn-prompt" data-function="' +  funcNo + '">NO</button>\
									</div>\
								</div>\
							</div>\
						</div>');
		$("#div-prompt").modal("show");
	}

	function prepareTables (page, pager = '') {
		$("table[data-topic]").each(function(){
			var table = $(this);
			var topic = table.data("topic");
			var paginator = (typeof table.data("paginator") !== "undefined") && (table.data("paginator").toUpperCase() == "YES");
			var checkboxes = (typeof table.data("checkboxes") !== "undefined") && (table.data("checkboxes").toUpperCase() == "YES");
			var noheader = (typeof table.data("header") !== "undefined") && (table.data("header").toUpperCase() == "NO");
			table.prop("id", "table-" + topic);
			table.data("page", page);
			table.css("table-layout", "fixed").css("width", "100%").css("word-wrap", "break-word");
			if (noheader) {
				table.find("thead").remove();
			}
			var h2 = table.closest("div.table-header");
			h2.html (h2.html() + " <span class='list-header'></span> <a href=\"javascript:;\" title=\"Refresh table\" onclick=\"updateList('" + page + "', '" + topic + "')\" style=\"color: white; margin-left: 10px\"><i class=\"fa fa-refresh\"></i></a>");
			if (paginator) {
				pager = "								<label class='select'><select class='table-per-page' data-topic='" + topic+ "' data-page='" + page+ "'>\
									<option value='25'>25</option>\
									<option value='50'>50</option>\
									<option value='100'>100</option>\
									<option value='500'>500</option>\
								</select><i></i><label>";
				table.closest("div").after("\
							<div class='col-md-2'>\
								<label>Per page</label>\
								" +  pager + "\
							</div>\
							<div class='col-md-10'>\
								<div class='pull-right'>\
									<div style='display: table; margin-top:20px' id='" + topic + "-paginator' class='table-paginator' data-topic='" + topic+ "' data-page='" + page+ "'>\
									</div>\
								</div>\
							</div>");
				for (i in perpage) {
					$("select[data-topic=" + i + "]").val(perpage[i]);
				}
			}

			if (checkboxes) {
				table.find("thead tr").prepend("<th style='width: 30px' class=''><input type='checkbox' class='row-check-all'></th>");
			}

// -------------- adding sort
			table.find("thead tr th[data-sort-field]").each(function(){
				var field = $(this).data("sort-field");
				$(this).removeAttr("sort-field");
				$(this).html('<span>' + $(this).text() + '</span> <a href="javascript:;" class="sort-selector" data-direction="" data-field="' + field + '" title="Sort by ' + $(this).text().toUpperCase() + '"><i class="fa fa-sort"></i></a>');
			})
//---------------------------
		})
	}

	function wrapTimeout (func = null, interval = null) {
		if (typeof func === "function") {
			func();
			if (interval) {
				tmId = setTimeout (function(){ wrapTimeout (func, interval) }, interval);
			}
		} else {
			if (typeof tmId !== "undefined") {
				clearTimeout(tmId);
			}
		}
	}


	function wrapTimeoutB (func = null, interval = null) {
		if (typeof func === "function") {
			func();
			if (interval) {
				tmBal = setTimeout (function(){ wrapTimeout (func, interval) }, interval);
			}
		} else {
			if (typeof tmBal !== "undefined") {
				clearTimeout(tmBal);
			}
		}
	}

	function checkAmount () {
		if (!$("#send-check").is(":checked")) {
			var fee = $("#fee").val();		
			var iBtc = parseInt(100000000*parseFloat($("#send-btc").val()) );
			var iFee = parseInt(100000000*parseFloat(fee));
			var iBalance = parseInt(100000000*btcBalance);
			var amount = iBtc + iFee;
			if (isNaN(amount) || amount>iBalance) {
				$("#send-btc").addClass("is-invalid");
			} else {
				$("#send-btc").removeClass("is-invalid");
			}
		}
	}