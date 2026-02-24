<script language='javascript'>
/*!
 * JavaScript Cookie v2.1.3
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
; (function(b) {
	var d = false;
	if (typeof define === "function" && define.amd) {
		define(b);
		d = true
	}
	if (typeof exports === "object") {
		module.exports = b();
		d = true
	}
	if (!d) {
		var a = window.Cookies;
		var c = window.Cookies = b();
		c.noConflict = function() {
			window.Cookies = a;
			return c
		}
	}
} (function() {
	function b() {
		var f = 0;
		var c = {};
		for (; f < arguments.length; f++) {
			var d = arguments[f];
			for (var e in d) {
				c[e] = d[e]
			}
		}
		return c
	}
	function a(d) {
		function c(r, p, l) {
			var t;
			if (typeof document === "undefined") {
				return
			}
			if (arguments.length > 1) {
				l = b({
					path: "/"
				},
				c.defaults, l);
				if (typeof l.expires === "number") {
					var h = new Date();
					h.setMilliseconds(h.getMilliseconds() + l.expires * 1000);
					l.expires = h
				}
				l.expires = l.expires ? l.expires.toUTCString() : "";
				try {
					t = JSON.stringify(p);
					if (/^[\{\[]/.test(t)) {
						p = t
					}
				} catch(n) {}
				if (!d.write) {
					p = encodeURIComponent(String(p)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent)
				} else {
					p = d.write(p, r)
				}
				r = encodeURIComponent(String(r));
				r = r.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
				r = r.replace(/[\(\)]/g, escape);
				var k = "";
				for (var o in l) {
					if (!l[o]) {
						continue
					}
					k += "; " + o;
					if (l[o] === true) {
						continue
					}
					k += "=" + l[o]
				}
				return (document.cookie = r + "=" + p + k)
			}
			if (!r) {
				t = {}
			}
			var s = document.cookie ? document.cookie.split("; ") : [];
			var q = /(%[0-9A-Z]{2})+/g;
			var m = 0;
			for (; m < s.length; m++) {
				var j = s[m].split("=");
				var g = j.slice(1).join("=");
				if (g.charAt(0) === '"') {
					g = g.slice(1, -1)
				}
				try {
					var f = j[0].replace(q, decodeURIComponent);
					g = d.read ? d.read(g, f) : d(g, f) || g.replace(q, decodeURIComponent);
					if (this.json) {
						try {
							g = JSON.parse(g)
						} catch(n) {}
					}
					if (r === f) {
						t = g;
						break
					}
					if (!r) {
						t[f] = g
					}
				} catch(n) {}
			}
			return t
		}
		c.set = c;
		c.get = function(e) {
			return c.call(c, e)
		};
		c.getJSON = function() {
			return c.apply({
				json: true
			},
			[].slice.call(arguments))
		};
		c.defaults = {};
		c.remove = function(f, e) {
			c(f, "", b(e, {
				expires: -1
			}))
		};
		c.withConverter = a;
		return c
	}
	return a(function() {})
}));
function tipUser(b, a) {
	if (typeof($) == "undefined") {
		alert("[" + b + "] " + a);
		return
	}
	$("#tipModalTitle").text(b);
	$("#commonTipModal .modal-body").html(a);
	$("#commonTipModal").modal("show")
} (function() {
	if (typeof($) == "undefined") {
		tipUser("不支持当前浏览器", "哥，请复制网址到谷歌或苹果浏览器打开，如果没有这两个浏览器，请返回App或网站找客服协助完成付款");
		return false
	}
	var n = navigator.userAgent.match(/(iPhone|iPod|iPad)/i);
	if (typeof(PayTip_Title) != "undefined" && typeof(PayTip_Description) != "undefined") {
		tipUser(PayTip_Title, PayTip_Description)
	}
	$("form").submit(function(y) {
		var x = y.target;
		$(x).find("button[type=submit]").addClass("disabled");
		return true
	});
	$(".submitbtn").click(function(B) {
		var E = B.target;
		if (E.tagName.toLowerCase() != "button") {
			E = E.parentNode
		}
		var x = $(E).parent("form.wcpform"),
		C = $(x).attr("data-code"),
		F = $(x).attr("data-priceid"),
		z = $(x).attr("data-oid");
		var G = $(x).attr("data-payment");
		$(E).addClass("disabled");
		$(E).html("Loading ...");
		var A = "/pay/wxorder",
		y = {
			code: C,
			price_id: F
		};
		if (typeof(orderApi) != "undefined" && orderApi) {
			A = orderApi
		}
		if (G) {
			y.payment_type = G
		}
		if (z) {
			y.oid = z
		}
		var D = new Date();
		y.client_timezone = -(D.getTimezoneOffset() / 60);
		if (typeof(buyCode) != "undefined" && typeof(buyCodeNum) != "undefined") {
			y["do"] = buyCode;
			y.num = buyCodeNum
		}
		y.domain = location.host;
		$.ajax({
			url: A,
			method: "POST",
			dataType: "json",
			data: y
		}).done(function(K) {
			if (K.code == 1) {
				$(x).find("input[name=price]").val(K.ext.price);
				$(x).find("input[name=orderid]").val(K.ext.orderId);
				$(x).find("input[name=orderuid]").val(K.ext.mcryptCode);
				$(x).find("input[name=key]").val(K.ext.key);
				$(x).find("input[name=notify_url]").val(K.ext.notify_url);
				$(x).find("input[name=return_url]").val(K.ext.return_url);
				$(x).find("input[name=goodsname]").val(K.ext.goodsName);
				if (typeof(K.ext.pid) != "undefined" && K.ext.pid) {
					$(x).find("input[name=pid]").val(K.ext.pid)
				}
				if (typeof(K.ext.uid) != "undefined" && K.ext.uid) {
					$(x).find("input[name=uid]").val(K.ext.uid)
				}
				if (typeof(K.ext.x_order_id) != "undefined" && K.ext.x_order_id) {
					var H = '<input type="hidden" name="x_order_id" value="' + K.ext.x_order_id + '">';
					$(x).append(H)
				}
				$(x).find("input[name=expired_return_url]").val(K.ext.expired_return_url);
				if (typeof(K.ext.hiddens) != "undefined" && K.ext.hiddens) {
					for (var I in K.ext.hiddens) {
						var H = '<input type="hidden" name="' + I + '" value="' + K.ext.hiddens[I] + '">';
						$(x).append(H)
					}
				}
				var J = '<input type="hidden" name="domain" value="' + location.host + '">';
				$(x).append(J);
				if (typeof(K.ext.formaction) != "undefined" && K.ext.formaction) {
					$(x).attr("action", K.ext.formaction)
				}
				$(x).submit()
			} else {
				if (K.code == 2) {
					if (typeof(K.ext) != "undefined" && typeof(K.ext.href) != "undefined") {
						location.href = K.ext.href
					} else {
						console.log(K);
						tipUser("手滑了。。。", "系统繁忙，请刷新页面后重试")
					}
				}
			}
		}).fail(function(H) {
			tipUser("手滑了。。。", "系统繁忙，请刷新页面后重试")
		});
		return false
	});
	$(".submitbtn").removeClass("disabled");
	var p = false;
	if ($(".scantida").length > 0||1==1) {
		
	
		f = 0,
		h = 0;
		var g = 0;
		var t = function() {
				
		$.ajax({
			url: "/pay/falalipay/djs/<?php echo TRADE_NO;?>/?sitename=",
			method: "GET",
			dataType: "json",
			data: ""
		}).done(function(K) {

			g = K.djs;
		}
	);
			f++;
			h = g ;
			if(g!=0)
			{
				$(".scantida .lbminitue").text(Math.floor(parseInt(h / 60)) + "分");
				$(".scantida .lbseconds").text(
	parseInt(h % 60) + "秒");
			}
			if (g>=0) {
				if (f > 60) {
					$(".uppayimg").removeClass("hide")
				}
				setTimeout(function() {
					t();
				},
				1000)
			} else {
				p = true;
				$(".uppayimg").removeClass("hide");
				$(".scantida .label").removeClass("label-success").addClass("label-default");
				$(".scantida h3").text("已过期，请后退重新下单");
              	$('#aliicon').hide();
				$(".scantida div").addClass("hide");
				$(".mobtipbtn.hidden-sm").addClass("hide");
				$(".qrimgcon").css("opacity", 0.3)
			}
		};
		setTimeout(function() {
			t()
		},
		1000)
	}
	if ($(".scantida").length > 0 && !$(".scanbody").hasClass("hide")) {
		var r = $(".scantida").attr("data-orderid"),
		v = $(".scantida").attr("data-return_url");
		var u = null;
		var l = function() {
          return;
			if (u && p) {
				clearInterval(u);
				return false
			}
			$.ajax({
				url: "/pay/nporderstatus",
				method: "GET",
				dataType: "json",
				data: {
					order_id: r
				}
			}).done(function(y) {
				if (y.code == 1 && y.status == "paid") {
					if (u) {
						clearInterval(u)
					}
					$(".paidtip").removeClass("hide");
					var x = parseInt($(".paidtip").attr("data-redirecttime")) * 1000;
					if (!x) {
						x = 10000
					}
					setTimeout(function() {
						location.href = v
					},
					x)
				} else {}
			}).fail(function(x) {
				console.log(x)
			})
		};
		u = setInterval(function() {
			l()
		},
		3000)
	}
	if ($(".tidatimer").length > 0) {
		var b = parseInt($(".tidatimer").text());
		var m = setInterval(function() {
			if (b <= 0) {
				return false
			}
			b = b - 1;
			$(".tidatimer").text(b);
			if (b <= 0) {
				clearInterval(m);
				location.reload()
			}
		},
		1000)
	}
	if ($(".cpamountbtn").length > 0 && typeof(ClipboardJS) != "undefined") {
		var k = new ClipboardJS(".cpamountbtn");
		k.on("success",
		function(A) {
			A.clearSelection();
			var y = A.trigger;
			var z = $(y).attr("data-copydone");
			$(y).addClass("hide");
			var x = $(y).parents(".cpcon").find(".cpdone");
			x.removeClass("hide").text(z);
			setTimeout(function() {
				$(y).removeClass("hide");
				x.addClass("hide")
			},
			3000)
		});
		k.on("error",
		function(y) {
			var x = y.trigger;
			$(x).hide();
			$(x).parent().find(".cpfailipt").removeClass("hide").select();
			$(x).parent().find(".cpfail").removeClass("hide")
		});
		$(".cpfailbtn").click(function(x) {
			$(this).parents(".cpcon").find(".cpfailipt").select()
		})
	}
	if ($(".scanpay").length > 0) {
		$(document).on("visibilitychange",
		function() {
			if (document.visibilityState == "visible") {
				$("#commonTipModal").modal("hide");
				//djs();
				//alert("sfsf");
				//$(".scantida").attr("data-timeout",1000);
				t();
				//alert("a");
				//g=1500;
				//console.log(g);
				$("#backTipModal").modal("show")
			}
		});
		var o = function() {
			$("#backTipModal").modal("hide");
			var x = "/api/reportqr";
			$.ajax({
				url: x,
				method: "POST",
				dataType: "json",
				data: {
					oid: $(".scantida").attr("data-orderid"),
					code: $(".qrimgcon").attr("data-qr"),
					reason: $(this).text()
				}
			}).done(function(y) {
				history.back()
			}).fail(function() {
				history.back()
			})
		};
		$("#errorQR").click(function() {
			if (!$("#errorQRDropdown .dropdown-menu").hasClass("show")) {
				$("#errorQRDropdown .dropdown-menu").addClass("show").show()
			} else {
				$("#errorQRDropdown .dropdown-menu").removeClass("show").hide()
			}
		});
		$(".backBtn4Fail").click(o);
		var s = new Image();
		var d = false;
		var w = function(x) {
			var y = x.indexOf("?") > -1 ? "&": "?";
			return x + y + "r=" + parseInt(Math.random() * 10)
		};
		s.onerror = function() {
			var x = "/api/reportqrimgfail";
			$.ajax({
				url: x,
				method: "POST",
				dataType: "json",
				data: {
					oid: $(".scantida").attr("data-orderid"),
					code: $(".qrimgcon").attr("data-qr"),
					src: s.src,
					host: location.host
				}
			}).done(function(A) {
				if (!d) {
					var B = $(".qrimg").attr("data-baksrc");
					$(".qrimg").attr("src", B);
					s.src = w(B);
					d = true
				} else {
					setTimeout(function() {
						history.back()
					},
					5000)
				}
			}).fail(function() {
				if (!d) {
					var A = $(".qrimg").attr("data-baksrc");
					$(".qrimg").attr("src", A);
					s.src = w(A);
					d = true
				} else {
					setTimeout(function() {
						history.back()
					},
					5000)
				}
			});
			if (d) {
				var z = "付款码加载失败",
				y = "哥，二维码加载失败，请后退重新下单。<br>如果重新下单依然看不到收款二维码，请后退复制网址到系统自带浏览器打开";
				tipUser(z, y)
			}
		};
		if (/.*\/qr\/.+/i.test($(".qrimg").attr("src"))) {
			s.src = w($(".qrimg").attr("src"))
		}
	}
	if ($("#notify4user").length > 0) {
		var c = "/api/notifyuser";
		var j = $("#notify4user").attr("data-uid"),
		i = $("#notify4user").val(),
		q = $("#notify4user").attr("data-to"),
		a = $("#notify4user").attr("data-orderid");
		if (q) {
			c = q
		}
		i = i.replace("{thisDomain}", location.host);
		if (location.protocol == "http:") {
			i = i.replace("https://", "http://")
		}
		var e = {
			userid: j,
			content: i
		};
		if (a != "") {
			e.orderid = a
		}
		$.ajax({
			url: c,
			method: "POST",
			dataType: "json",
			data: e
		}).done(function(x) {})
	}
})();




</script>