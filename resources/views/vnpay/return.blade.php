<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Success!</title>

    <style>
        .modal {
            width: 100vw;
            height: 110vh;
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: -70px;
            font-family: Open Sans;
        }
        .modal-container {
            width: 400px;
            height: 290px;
            background-color: white;
            box-shadow: 0px 0px 8px 0px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 25px 25px 35px 25px;
            justify-content: space-around;
        }
        .title {
            font-size: 30px;
            font: 600;
            line-height: 0;
            text-align: center;
            color: #3c7026;
        }
        .content {
            display: flex;
            align-items: center;
            flex-direction: column;
        }
        img {
            background-color: black;
            width: 100px;
            height: auto;
        }
        .title-success {
            font-size: 13px;
            text-align: center;
        }
        .confirm {
            padding: 7px 50px;
            border-radius: 20px;
            outline: none;
            background-color: #3c7026;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        a {
            color:aqua;
        }
    </style>
</head>
<body>
<div class="modal">
    <div class="modal-container">
        <p class="title">Successful account renewal!</p>
        <div class="content">
            <img
                src="https://cdn.pixabay.com/photo/2018/07/19/06/05/handshake-3547921_1280.jpg"
            />
            <div class="paymentVnPay">
                <p class="title-success">Payment successfully</p>
            </div>
            <div class="btn-paymentVnPay">
                <button class="confirm"><a href="https://decoplantsflowers-f381a.web.app/">Go to the website now!</a></button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
