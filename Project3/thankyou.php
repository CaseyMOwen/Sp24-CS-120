<?php session_start();?>
<!doctype html>
<html>
<head>
<link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">"> 
    <meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">
<title>The Floral Arrangement</title>
<style type="text/css">
    h1{margin-top: 75px; text-align: center;}
    #order_details{margin-left: 15%; margin-top: 50px; font-size: 22px;}
    #continue_button{text-align: center;}
    #continueshopping{ font-size: 25px;}
    #datestring{display: inline; font-weight: bold;}
    .background{background-image: url(images/background.webp); background-size:cover; object-fit: cover; padding-top: 15px; height: 95%;}
    body{height: 100%;}
    html{height: 100%;}

    @media screen and (max-width: 1050px) {
        h1{margin-top: 120px;}
    }

    @media screen and (max-width: 850px) {
        #order_details{margin: 50px auto; width: 80%;}
    }

    @media screen and (max-width: 600px) {
        h1{margin-top: 220px;} 
    }

</style>
<script   src="https://code.jquery.com/jquery-3.1.1.min.js"   
integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="   
crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<script>

    $(document).ready(function() {
        <?php 
            $session_data = json_encode($_SESSION);
            echo "session_data = $session_data;";
            $cart_total = $_GET["cart_total"];
            echo "cart_total = $cart_total;";

            $configs = include('config.php');
            $server = $configs['server'];
            $userid = $configs['userid'];
            $pw = $configs['pw'];
            $db = $configs['db'];
            $conn = new mysqli($server, $userid, $pw);
            
                
            // Need to first check that something was ordered - handling case of this page being refreshed, and session variables are empty

            $something_ordered = TRUE;
            if ($_SESSION == []) {
                $something_ordered = FALSE;
            }

            $conn->select_db($db);
            $orders_sql = "INSERT INTO `orders` (`id`, `time`) VALUES (NULL, NOW())"; 

            if ($something_ordered) {
                $conn->query($orders_sql);
            }

            $orderid = $conn->insert_id;
            $ordereditems_sql = "INSERT INTO `ordereditems` (`order_id`, `item_id`, `quantity`) VALUES";
            foreach ($_SESSION as $key=>$value) {
                if (str_starts_with($key, 'qty_')) {
                    $product_id = ltrim($key, 'qty_');
                    $product_qty = $_SESSION[$key];
                    if ($product_qty == 0) {
                        continue;
                    }
                    $ordereditems_sql .= "('$orderid', '$product_id', '$product_qty'), ";
                    $num_items_ordered++;
                }
            }
            $new_ordereditems_sql = rtrim($ordereditems_sql, ", ");
            if ($something_ordered) {
                $conn->query($new_ordereditems_sql);
            }
            $conn->close();
            $_SESSION = [];
            ?>
        cart_qtys = {}
        for (key in session_data) {
            console.log(key + ": " + session_data[key])
            if (key.startsWith('qty_')) {
                id = key.slice(4)
                cart_qtys[id] = session_data[key]
            }
        }
        now = new Date()
        writeDetails(cart_total, now)
    })

    function continueShopping() {
        window.location.href = 'products.php';
    }

    

    function writeDetails(cart_total, now) {
        ship_date = new Date()
        ship_date.setDate(now.getDate() + 2)
        options = {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"
        }
        datestring = '<div id="datestring">' + ship_date.toLocaleDateString("en-US", options) + '</div>'
        detailsDiv = ''
        detailsDiv += '<div>Thank you for your order!</div>'
        detailsDiv += '<div> Your order is expected to ship in 2 days, on ' + datestring + '.</div>'
        detailsDiv += '<div> Order Total: $' + cart_total.toFixed(2) + '</div>'
        $('#order_details').html(detailsDiv)
    }

    function setSessionVar(key, value) {
        request = new XMLHttpRequest();
        request.open("POST", "setsessionvar.php", true);
        request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        request.onreadystatechange = function() {
            if (request.readyState == 4 && request.status == 200) {
                console.log(request.responseText)
                }
            else if (request.readyState == 4 && request.status != 200) {
                console.log(request.status)
                console.log('Request Failed!')
            }
        }
        data = key + '=' + value
        request.send(data);
    }
</script>

</head>
<body>
<div class="header">
    <div class="nav">
        <div class="logo"><div class="logo_img"></div><div id="logo_text">The Floral Arrangement</div></div>
        <div class="header_text"><a href="products.php">Products</a></div>
        <div  class="header_text"><a href="orders.php">Order History</a></div>
        <div class = "header_text" id="cart"><a href='cart.php'><img src='images/carticon.png'></a></a></div>
</div>
</div>
<div class="background">
<h1>Thank You!</h1>
<div id="order_details">
</div>
<div id='continue_button'>
<input type="button" value = "Continue Shopping" id="continueshopping" onclick="continueShopping()">
</div>
</div>
<div class="footer">
    <div><a href="tel:1234567890">123-456-7890</a></div>
    <div id="social_links">    
        <a href='https://www.instagram.com' target="_blank"><img src='images/instagram.webp'></a>
        <a href='https://www.twitter.com' target="_blank"><img src='images/twitter.png'></a>
        <a href='https://www.facebook.com' target="_blank"><img src='images/facebook.png'></a>
    </div>
    <div><a href="mailto:email@example.com">email@example.com</a></div>
    </div>
</body>