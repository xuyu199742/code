<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <title>Document</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        .clearfix :before,
        .clearfix :after {
            content: '';
            display: block;
            clear: both;
        }

        .clearfix {
            zoom: 1
        }

        .box {
            text-align: center;
        }

        .logo {
            border-bottom: 1px solid #ccc;
        }

        .logo img {
            width: 100px;
        }

        .money {
            margin-top: 20px;
            font-size: 30px;
        }

        #countdown {
            margin: 10px auto;
            width: 210px;
            border: 2px solid #e60606;
            color: #e60606;
            border-radius: 4px;
        }

        #countdown p:nth-child(2) {
            font-weight: 700;
            margin: 5px 0;
        }

        .order {
            font-size: 12px;
            font-weight: 700;
        }

        .aipayImg {
            width: 375px;
            height: 220px;
            position: relative;
            color: #e60606;
            font-weight: 700;
            margin: 0 auto;
        }

        .aipayImg .left {
            position: absolute;
            left: 50px;
        }

        .aipayImg .middle {
            position: absolute;
            left: 50%;
            transform: translate(-50%);
        }

        .aipayImg img {
            width: 200px;
        }

        .aipayImg .right {
            position: absolute;
            right: 50px;
        }

        .mark p:nth-child(1) {
            color: #e60606;
        }

        .mark p:nth-child(2) {
            color: #409eff;
        }

        .account {
            font-weight: 700;
            margin: 15px 0;
        }

        .account button {
            width: 50px;
            height: 25px;
            background-color: #409eff;
            color: #fff;
            border: none;
            border-radius: 5px;
        }

        .username {
            font-weight: 700;
        }

        .username button {
            width: 50px;
            height: 25px;
            background-color: #409eff;
            color: #fff;
            border: none;
            border-radius: 5px;
        }

        .button button {
            margin: 20px 0;
            width: 200px;
            height: 35px;
            font-weight: 700;
            background-color: #409eff;
            color: #fff;
            border: none;
            border-radius: 5px;
        }

        .button p {
            font-size: 14px;
            color: #e60606;
            font-weight: 700;
        }

        .button span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #e60606;
            margin-right: 5px;
        }
    </style>
</head>

<body>
<div class="box">
    <div class="logo">
        <img src="data:image/png;base64,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" alt="">
    </div>
    <div class="money">
        <span>&yen; <?php echo $amount; ?></span>
    </div>
    <div id="countdown">
        <p>过期时间剩余</p>
        <p id="LeftTime"></p>
        <p>请勿超过此时间支付！！</p>
    </div>
    <div class="order">
        <span>订单号：<?php echo $order_no; ?></span>
    </div>
    <div class="aipayImg clearfix">
        <div class="left">
            <span>重<br />复<br />扫<br />码<br />，<br />概<br />不<br />赔<br />付</span>
        </div>
        <div class="middle">
            <img src="<?php echo $image; ?>" alt="">
        </div>
        <div class="right">
            <span>超<br />时<br />扫<br />码<br />，<br />概<br />不<br />赔<br />付</span>
        </div>
    </div>
    <div class="mark">
        <p>请使用后置摄像头扫码</p>
        <!--      <p>请复制支付宝账号打开支付宝进行转帐</p>-->
        <!-- <div class="account">
          <span>账号：16573726995</span> <button>复制</button>
        </div>
        <div class="username">
          <span>真实姓名：陈志</span> <button>复制</button>
        </div> -->
    </div>
    <div class="button">
        <button type="submit" id="btn" onclick="btn()">打开支付宝</button>
        <p><span></span>付款10分钟后如未到账，请及时联系客服处理</p>
    </div>
</div>

<script>
    var endtime = new Date().getTime() + 60*10*1000;//结束时间
    function FreshTime()
    {
        var nowtime = new Date();//当前时间
        var lefttime = parseInt((endtime - nowtime.getTime()) / 1000);
        // d = parseInt(lefttime / 3600 / 24);
        // h = parseInt((lefttime / 3600) % 24);
        m = parseInt((lefttime / 60) % 60);
        s = parseInt(lefttime % 60);

        document.getElementById("LeftTime").innerHTML = m + "分" + s + "秒";
        if (lefttime <= 0) {
            document.getElementById("LeftTime").innerHTML = "二维码已失效";
            clearInterval(sh);
        }
    }
    FreshTime()
    var sh;
    sh = setInterval(FreshTime, 1000);
    function btn()
    {
        location.href = '<?php echo $pay_url ?>'
        // location.href = 'alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount=0.01&userId=2088222392656452&memo=备注'
    }
</script>
</body>

</html>
