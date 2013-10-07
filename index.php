<html>
    <head>
        <title>MeetMap</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Info window with <code>maxWidth</code></title>
    <link href="https://developer.google.com/maps/documentation/javascript/examples/default.css" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <style>
        form{margin:0}
        .fl{float:left}
        .fr{float:right}
        .clear{clear:both}
        .header{height:50px;background-color:gray}
        .header .logo{font-size:30px;font-weight:bold;color:white;margin:5px 0 0 10px;}
        .header .search{margin:10px 10px 0 0;}
        .header .search input{border:none;height:20px}
        .header .search input[type="submit"]{margin-left:-6px}
    </style>
    <script>
    function initialize() {
        var Center = new google.maps.LatLng(37.4,-122);
        var mapOptions = {
            zoom: 11,
            center: Center,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
        var cirs = [];
<?php
function utf8_clean($str) {
    return iconv('UTF-8', 'UTF-8//IGNORE', $str);
}

function load($keyword, $type)
{
    $content = "";
    $save_flag = false;
    if(file_exists($type.'/'.$keyword))
        $f = fopen($type.'/'.$keyword, 'r');
    else
    {
        if($type == 'topics')
            $f = fopen("https://api.meetup.com/topics?&sign=true&search=$keyword&page=20&key=3c615f272ad7d3f77d476561341", "r"); 
        else
            $f = fopen("https://api.meetup.com/2/open_events?&sign=true&topic=$keyword&page=20&key=3c615f272ad7d3f77d476561341", "r");
        $save_flag = true;
    }
    while(!feof($f))
        $content .= fread($f, 40960);

    fclose($f);
    if($save_flag)
    {
        $f = fopen($type.'/'.$keyword, 'w');
        $content = utf8_clean($content);
        fwrite($f, $content);
        fclose($f);
    }
    return $content;
}

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $content = load($_POST['keyword'], 'topics');
    $list = json_decode($content, true);
    $list = $list['results'];
    $cnt = 0;
    foreach($list as $item)
    {
        $content = load($item['urlkey'], 'events');
        $lt = json_decode($content, true);
        if($lt != NULL)
        {
            $lt = $lt['results'];
            foreach($lt as $i)
            {
                $cnt++;
                if(!isset($i['venue']))continue;
                $name = "place" . $cnt;
                $mk_name = "marker" . $cnt;
                $info_win = "info_win" . $cnt;
                $content = "content" . $cnt;
                $i['name'] = addslashes($i['name']);
?>
                var <?=$name?> = new google.maps.LatLng(<?=$i['venue']['lat']?>, <?=$i['venue']['lon']?>);
                var <?=$mk_name?> = new google.maps.Marker({
                    position: <?=$name?>,
                    map: map,
                    title: '<?=$i['name']?>'
                });
                var <?=$content?> = '<div id="content">' +
                    '<a href="<?=$i["event_url"]?>" target="_blank"><h5 id="siteNotice">'+
                    '<?=$i['name']?>'+
                    '</h5></a>'+
                    '<p><strong>status:</strong><?=$i['status']?></p>'+
                    '<p><strong>date:</strong><?=date("F j, Y, g:i a", $i["time"]/1000 )?></p>' ;
                var <?=$info_win?> = new google.maps.InfoWindow({
                    content: <?=$content?>,
                    maxWidth: 210
                });

                google.maps.event.addListener(<?=$mk_name?>,'mouseover',function(){
                    <?=$info_win?>.open(map,<?=$mk_name?>);
                });

                google.maps.event.addListener(<?=$mk_name?>,'mouseout',function(){
                    window.setTimeout(function(){
                        <?=$info_win?>.close(map,<?=$mk_name?>)
                    },1000);
                });

                var cir_opt = {
                    strokeColor: '#153672',
                    strokeOpacity: 0.16,
                    strokeWeight: 3.5,
                    fillColor: '#153672',
                    fillOpacity: 0.4,
                    map: map,
                    center: <?=$name?>,
                    radius: (250+<?=$i['yes_rsvp_count']?>)*30000 / (map.getZoom() * map.getZoom() * map.getZoom()* map.getZoom())
                }
                var cir = new google.maps.Circle(cir_opt);
                cir.population = <?=$i['yes_rsvp_count']?>;
                cirs.push(cir);
<?php
           }
       }
    }
}
?>
    google.maps.event.addListener(map, 'zoom_changed', function() {
                for (var eventN in cirs) {
                var newR = (cirs[eventN].population +250) * 30000 / (map.getZoom() * map.getZoom() * map.getZoom() * map.getZoom());
                cirs[eventN].setRadius(newR);
                }
    });
}
    google.maps.event.addDomListener(window, 'load', initialize);
    </script>

    </head>
    <body>
        <div class="header">
            <div class="logo fl">MeetMap</div>
            <div class="search fr">
                <form action="" method="post" >
                <input type="text" name="keyword" value="<?php if(isset($_POST['keyword'])) echo $_POST['keyword'];?>"/>
                    <input type="submit" value="Go" />
                </form>
            </div>
        </div>
        <div id="map-canvas" class="clear"></div>
    </body>
</html>
