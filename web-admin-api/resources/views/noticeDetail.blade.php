<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>content</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../../css/rest.css">
    <link rel="stylesheet" href="../../css/content.css">
</head>
<body>
<div class="bodyDiv"  style="background:{{ $bgColor }}">
    <div class="contentDiv">
        <div class="textP">
            @if($data['is_img'] == 1)
                <img src="{{$data['img']}}"  style="margin: 0 auto;display: block;width:100%"/>
            @else
                <?php echo($data['content']);?>
            @endif
        </div>
    </div>
</div>
</body>
<script src="../../js/jquery-3.4.1.min.js"></script>
<script>
    function redirectUrl(url)
    {
        top.location.href = url;
    }
    function refresh(){
        var w =  document.body.clientWidth;//获取页面可见高度
        for (var i =0 ;i <document.getElementsByClassName('titleImg').length;i++){
            document.getElementsByClassName('titleImg')[i].style.height =w*0.302+'px';
        }
    }
    window.addEventListener("resize", refresh);
    refresh()
</script>
</html>
