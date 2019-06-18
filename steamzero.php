<?php
	if (isset($_REQUEST['steamapireq'])) {
		// Let's go to Steam API!
		$api_url = "https://api.steampowered.com" . $_REQUEST['steamapireq'];
		$json = file_get_contents($api_url);
		if ($json === false) {
			http_response_code(500);
			print("Call to $api_url failed!");
			exit(1);
		}
		print($json);
		exit(0);
	}
?>
<!doctype html>
<html lang=en>
   <title>Steam-Zero</title>
   <meta charset=utf-8>
   <link href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAJ1BMVEX///8SVaESVaESVaESVaESVaESVaESVaESVaESVaESVaG4zOP///9Yn1YFAAAACnRSTlMABxNWr7bR1d/761gaCAAAAFpJREFUeNplj1EKACEIBXXV1Or+591nwcLifMhzoFQCLOoRrsJ0eCznIe05/ZgfA4YNYW2wEIxJsvoFyqSQwlcsjaLkf+EUV+x9RTTRnrRP29i2WFu9HdfOfwFHrAgNTBwYSAAAAABJRU5ErkJggg=="rel=icon type=image/png>
   <style>.gr td,.half{vertical-align:top}.half,.nav,body,html,input{box-sizing:border-box}html{padding:0;background:#fff}html,input{font:16px Segoe UI,Droid Sans,sans-serif;line-height:1.5;color:#555}.t th,h2{font-weight:400}body{padding:0 22rem 4rem}body,html{min-height:100%;margin:0}.tm{margin:0 -1rem}table{border-collapse:collapse}.r2>tbody>tr>:nth-child(2),.r3>tbody>tr>:nth-child(3),.r4>tbody>tr>:nth-child(4){text-align:right}.t td,.t th{padding:.3rem 1rem}.t td{border-top:1px solid #eee;border-bottom:1px solid #eee}.t th{position:relative;text-align:inherit;color:#bbb;border-left:1px solid #eee;cursor:pointer}.t th:hover{background:#f6fcff}.t th:first-child{border-left:none}.ord::before{content:'▼';position:absolute;left:.5rem}.big,.nav,.wait{position:fixed;top:0;bottom:0}.ord.d::before{content:'▲'}.ord.ro::before{left:auto;right:.5rem}.t tr:first-child:hover{background:0 0}.t tr:hover{background:#ffd}.gr td{width:33.3%;padding:0 0 2rem 1rem}.gr td:first-child{padding-left:0}.gr td>i{display:block;font-size:2rem;color:#bbb}.big input,h2{font-size:1.5rem}.gl>div{clear:both}.gl>div>img{float:left;margin:0 .5rem 1.5rem 0}h2{margin:1.5rem 0}i{font-style:normal}a{color:#4be;text-decoration:none;border-bottom:1PX solid rgba(68,187,238,.5)}.nav{z-index:10;width:17rem;padding:0 1.5rem 2rem}.half{display:inline-block;width:47%}.mr{margin:0 6% 0 0}.w100{width:100%}.l{float:left}.fl{left:0}.fr{right:0}.h{display:none}.wait{color:#fff;background:rgba(0,0,0,.5)}.big,.wait{display:flex;align-items:center;justify-content:center;right:0;left:0}.wait:before{content:'';display:block;position:absolute;width:5rem;height:5rem;border:4px solid #000;border-top:4px solid #fff;border-radius:100%;background:#000;opacity:.4;animation:spin 2s linear infinite}@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}.big{z-index:20;text-align:center}input{width:100%;padding:.45rem 1rem .55rem;border:none;margin:0 0 .3rem}input[type=button],input[type=submit]{background:#590;color:#de8;cursor:pointer}input[type=button]:hover,input[type=submit]:hover{background:#6a1}.big>div{margin:-8rem 0 0}.big input{padding:.7rem 1.5rem .9rem;margin:0 0 .5rem}.big,.nav{background:#345;background:linear-gradient(#525a66,#1e2936);color:#fff}.fx{display:flex;flex-direction:column}.fx>i{flex:1 1 auto}</style>
   <div class="nav fl"></div>
   <div class="half mr">
      <h2>Achievements stats</h2>
      <table class="w100 gr"id=tAStat></table>
      <h2>Recently games</h2>
      <div class=gl id=dRecently></div>
   </div>
   <div class=half>
      <h2>Achievements in games (%)</h2>
      <div class=tm>
         <table class="w100 r2 r3 r4 t"id=tGames></table>
      </div>
   </div>
   <div class="nav fr fx">
      <div id=dUser></div>
      <i></i>
      <form id=fChange onsubmit='FindUser(event,document.getElementById("iProfile").value)'><input id=iProfile placeholder="ProfileName or ID"> <input type=submit value="Change profile"><br><br><input type=button value="Update statistics"onclick=UpdateStat()></form>
   </div>
   <form id=fAuth onsubmit='FindUser(event,document.getElementById("iName").value)'class=h>
      <div>Enter Steam profile URL name (steamcommunity.com/id/<b>ProfileName</b>):<br><br><input id=iName placeholder=ProfileName autofocus> <input type=submit value=Find><br><br>Works with <a href="https://support.steampowered.com/kb_article.php?ref=4113-YUDH-6401"target=_blank>public profiles</a> only.</div>
   </form>
   <div class=h id=dWait></div>
   <script>
var achievements = {},
    achievementsSort = [],
    ordCol = 1,
    ordDir = -1,
    gameCounter = 0,
    key = "",
    deadPool = [];

function Init() {
	key = getParameterByName('key');
	if (!key)
		alert('Need Steam API key in query parameter! Hint: ?key=<your key>');
    localStorage.userId ? Set() : document.getElementById("fAuth").className = "big"
}

function Set() {
    GetInfo();
	GetRecently();
	GetGames();
}

function getParameterByName(name, url) {
    if (!url)
		url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results)
		return null;
    if (!results[2])
		return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function FindUser(e, t) {
    e.preventDefault();
	KillAJAX();
    var o = function(e) {
        e != {} && 1 == e.response.success ? (
			delete localStorage.achievements,
			achievementsSort = [],
			document.getElementById("tGames").innerHTML = "",
			document.getElementById("tAStat").innerHTML = "",
			localStorage.userId = e.response.steamid,
			Set(),
			document.getElementById("fAuth").className = "h"
		) : alert("Profile not found.")
    };
    17 == (t = t.trim()).length && t.match(/^\d+$/) ? o({
        response: {
            success: 1,
            steamid: t
        }
    }) : AJAX("/ISteamUser/ResolveVanityURL/v1/?key=" + key + "&vanityurl=" + encodeURIComponent(t), o)
}

function UpdateStat() {
    delete localStorage.achievements, location.reload()
}

function GetInfo() {
    AJAX("/ISteamUser/GetPlayerSummaries/v2/?key=" + key + "&steamids=" + localStorage.userId, function(e) {
        var t = e.response.players[0],
            o = 1e3 * t.timecreated,
            a = "",
            n = "";
        t.loccountrycode && (a = "<br>User region: " + t.loccountrycode), t.gameextrainfo && (n = "<br>Now playing:<br>" + t.gameextrainfo), document.getElementById("dUser").innerHTML = '<h2><img src="' + t.avatar + '" class="l">&nbsp;' + t.personaname + '</h2><a href="' + t.profileurl + '" target="_blank">To Steam profile</a><br><br>Since ' + new Date(o).toLocaleDateString() + " (" + Number(((Date.now() - o) / 315576e5).toFixed(1)).toLocaleString() + " years)" + a + n
    })
}

function GetRecently() {
    AJAX("/IPlayerService/GetRecentlyPlayedGames/v1/?key=" + key + "&steamid=" + localStorage.userId, function(e) {
        var t = e.response.games,
            o = 0,
            a = "";
        t.forEach(function(e) {
            o += e.playtime_2weeks, a += '<div><img src="http://media.steampowered.com/steamcommunity/public/images/apps/' + e.appid + "/" + e.img_logo_url + '.jpg"> ' + e.name + "<br>" + Number((e.playtime_2weeks / 60).toFixed(2)).toLocaleString() + "/" + Number((e.playtime_forever / 60).toFixed(2)).toLocaleString() + " (hours last 2 weeks/forever)</div>"
        }), a = "Played " + t.length + "&nbsp;games and&nbsp;" + Number((o / 60).toFixed(2)).toLocaleString() + "&nbsp;hours for&nbsp;last 2&nbsp;weeks:<br><br>" + a, document.getElementById("dRecently").innerHTML = a
    })
}

function GetGames() {
    AJAX("/IPlayerService/GetOwnedGames/v1/?key=" + key + "&steamid=" + localStorage.userId, function(e) {
        if (localStorage.achievements) {
            for (var t in achievements = JSON.parse(localStorage.achievements))
				0 != achievements[t] && achievementsSort.push(achievements[t]);
            DrawAchievements()
        } else achievements = {};
        e.response.games && (gameCounter = parseInt(e.response.game_count), ShowWait("0%"), e.response.games.forEach(function(o) {
            void 0 !== achievements[o.appid] ? CountDown() && ShowWait("", !0) : AJAX("/ISteamUserStats/GetPlayerAchievements/v1?key=" + key + "&steamid=" + localStorage.userId + "&appid=" + o.appid, function(e) {
                if (e.playerstats && e.playerstats.achievements) {
                    var t = 0;
                    e.playerstats.achievements.forEach(function(e) {
                        1 == e.achieved && t++
                    }), achievements[o.appid] = [e.playerstats.gameName.replace(/^The /, ""), Math.floor(t / e.playerstats.achievements.length * 100), t, e.playerstats.achievements.length, parseFloat(o.playtime_forever / 60).toFixed(2)], achievementsSort.push(achievements[o.appid]), DrawAchievements()
                } else {
					achievements[o.appid] = [];
					DrawAchievements();
				}
            }, function() {
                void 0 === achievements[o.appid] && (achievements[o.appid] = 0), ShowWait(Math.floor(100 - gameCounter / e.response.games.length * 100) + "%"), CountDown() && ShowWait("", !0)
            })
        }))
    })
}

function CountDown() {
    var e = !1;
    return 0 == --gameCounter && (localStorage.achievements = JSON.stringify(achievements), DrawAchievements(), e = !0), e
}

function DrawAchievements() {
    var e = ' class="ord' + (1 == ordDir ? " d" : "") + (0 == ordCol ? " ro" : "") + '"',
        t = "<tr><th" + (0 == ordCol ? e : "") +
			' onclick="Ord(0)">Game</th><th' + (1 == ordCol ? e : "") +
			' onclick="Ord(1)">%</th><th' + (3 == ordCol ? e : "") +
			' onclick="Ord(2)" style="min-width: 4.5rem">Achieved</th><th' + (4 == ordCol ? e : "") +
			' onclick="Ord(4)">Time</th></tr>',
        o = 0,
        a = 0,
        n = 0,
        r = 0,
        i = 0,
        s = 0,
        c = 0,
        d = 0,
        m = 0;
    achievementsSort.order(ordCol, ordDir),
	achievementsSort.forEach(function(e) {
        t += "<tr><td>" + e[0] + "</td><td>" +
			e[1] + "%</td><td>" +
			e[2] + "/" +
			e[3] + "</td><td>" +
			Number(e[4]).toLocaleString() +
			"&nbsp;h</td></tr>",
		100 == e[1] && c++,
		75 < e[1] && d++,
		50 < e[1] && m++,
		0 < e[4] && (s++, r += e[2], i += e[3], o += e[1], a++,
		n += parseFloat(e[4]))
    }), document.getElementById("tAStat").innerHTML = "<tr><td><i>" +
		r.toLocaleString() + "</i>Achievements out&nbsp;of&nbsp;" +
		i.toLocaleString() + " (" + Math.floor(r / i * 100) + "%)</td><td><i>" +
		s.toLocaleString() + "</i>Games with&nbsp;achievements</td><td><i>" +
		Math.floor(o / a) + "%</i>Average percentage of&nbsp;achievements</td></tr><tr><td><i>" +
		Math.round(n).toLocaleString() + "</i>Hours in&nbsp;games with&nbsp;achievements</td><td><i>" +
		Number((r / n).toFixed(1)).toLocaleString() +
		"</i>Achievements received per&nbsp;hour (average)</td><td></td></tr><tr><td><i>" +
		Number((r / a).toFixed(1)).toLocaleString() +
		"</i>Achievements received per&nbsp;game (average)</td><td><i>" +
		Number((i / a).toFixed(1)).toLocaleString() +
		"</i>Achievements number per&nbsp;game (average)</td><td><i>" +
		(achievementsSort.length - a).toLocaleString() +
		"</i>Achievements games not&nbsp;played (0 hours)</td></tr><tr><td><i>" +
		c.toLocaleString() +
		"</i>Perfect games<br>(100%&nbsp;achieved)</td><td><i>" +
		d.toLocaleString() +
		"</i>Very good games<br>(>&nbsp;75% achieved)</td><td><i>" +
		m.toLocaleString() + "</i>Good games<br>(>&nbsp;50% achieved)</td></tr>",
	document.getElementById("tGames").innerHTML = t
}

function ShowWait(e, t) {
    var o = document.getElementById("dWait");
    o.innerHTML = e, o.className = t ? "h" : "wait"
}

function Ord(e) {
    ordCol == e ? ordDir *= -1 : (ordDir = -1, ordCol = e),
	DrawAchievements()
}

function AJAX(e, t, o) {
    var a;
	var currentPage;
    window.XMLHttpRequest ? a = new XMLHttpRequest : window.ActiveXObject && (a = new ActiveXObject("Microsoft.XMLHTTP"));
	deadPool.push(a);
	4 == a.readyState || 0 == a.readyState ? (
		currentPage = window.location.href.split('?')[0],
		a.open("GET", currentPage + '?steamapireq=' + encodeURIComponent(e), true),
		a.withCredentials = true,
		a.onreadystatechange = function() {
			if (4 == a.readyState) {
				if (200 == a.status) {
					t(JSON.parse(a.responseText));
				} else {
					t({});
				}
				o && o();
			}
		},
		a.send()) :
	setTimeout(function() {
		AJAX(e, o)
	}, 500)
}

function KillAJAX() {
    deadPool.forEach(function(e) {
        e.abort()
    }), deadPool = []
}

Init(),
Array.prototype.order = function(a, n) {
    this.sort(function(e, t) {
        var o = 0;
        return 0 < a ?
			0 == (o = e[a] - t[a]) && (o = e[0].toLowerCase() < t[0].toLowerCase() ? 1 : -1) :
			o = e[0].toLowerCase() > t[0].toLowerCase() ? 1 : -1, o * n
    })
}
   </script>

</html>