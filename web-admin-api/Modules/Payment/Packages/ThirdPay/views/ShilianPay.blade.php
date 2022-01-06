<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>充值界面</title>
    <script src="/static/frontend/js/jquery.min.js"></script>
    <script src="/static/frontend/js/jquery.qrcode.min.js"></script>
    <style>
        .qcode {
            border: 1px solid #0C0C0C;
            margin: 20px auto;
            width: 300px;
            height: 300px;
        }
    </style>
</head>
<body>
@if(isset($return_data['pay_url']) && $return_data['pay_url']!='')
        <p style="text-align: center;margin: 200px auto 0px;">扫二维码支付</p>
        <div class="qcode">
            <img src="{{$return_data['pay_url']}}" alt="" width="300" height="300" alt="二维码">
        </div>
@else
    <p style="text-align: center;margin: 100px auto 0px;color:red;font-size: 18px;">订单已生成，跳转支付失败！！！</p>
@endif
</body>
</html>
