<?php session_start();?>
<!doctype html>
<html>
<head>
<link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">"> 
    <meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">
<title>The Floral Arrangement</title>
<style type="text/css">
    h1{margin-top: 75px; text-align: center;}
    #orders_content{width: 950px; margin: 25px auto; text-align: center; font-size: 22px; border-collapse: collapse;}
    .orders_row{border-bottom: 1px solid black;}
    .orders_row td{padding: 15px 0px;}
    .orders_headers{border-bottom: 2px solid black;}
    .orders_headers th{ padding-bottom: 15px;}
    #buttons{text-align: center; margin-bottom: 100px;}
    #buttons input{font-size: 25px; margin: 0 50px;}
    .background{background-image: url(images/background.webp); background-size:cover; object-fit: cover; padding-top: 15px; height: 95%;}
    body{height: 100%;}
    html{height: 100%;}

    @media screen and (max-width: 1050px) {
        h1{margin-top: 120px;}
    }

    @media screen and (max-width: 1000px) {
        #orders_content{width: 95%; min-width: 500px;}
    }

    @media screen and (max-width: 700px) {
        h1{font-size: 35px;}
        #orders_content{font-size: 18px;}
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
        // Array of Order objects
        orders = []
        // Key is id of order, value is index in orders array
        orders_idx_by_id = {}
        <?php 
            $configs = include('config.php');
            $server = $configs['server'];
            $userid = $configs['userid'];
            $pw = $configs['pw'];
            $db = $configs['db'];
            
            $conn = new mysqli($server, $userid, $pw);
            $conn->select_db($db);
            $sql_orders = "SELECT * FROM orders ORDER BY orders.time DESC";
            $result = $conn->query($sql_orders);

            $idx = 0;
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $timestamp = $row['time'];
                    echo <<<END
                    order = new Order($id, "$timestamp");
                    orders.push(order);
                    orders_idx_by_id[$id] = $idx;
                    END;
                    $idx++;
                }
            } else {
                echo "no results";
            }

            $sql_items_ordered = "SELECT oi.order_id, oi.item_id, p.name, oi.quantity, p.price FROM ordereditems AS oi, products AS p WHERE oi.item_id = p.id";
            $result = $conn->query($sql_items_ordered);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $order_id = $row['order_id'];
                    $item_id = $row['item_id'];
                    $name = $row['name'];
                    $quantity = $row['quantity'];
                    $price = $row['price'];
                    echo <<<END
                    item = new ItemOrdered("$name", $quantity, $price);
                    
                    orders[orders_idx_by_id[$order_id]].addItem($item_id, item);
                    END;
                }
            } else {
                echo "no results";
            }
            $conn->close();
        ?>
        writeOrders(orders)
    })
    
    // Items ordered is object of ItemOrdered objects
    function Order(id, timestamp) {
        this.id = id
        this.items_ordered = {}
        this.total = 0
        
        this.addItem = function(item_id, item) {
            this.items_ordered[item_id] = item
            this.total += this.items_ordered[item_id].item_total
        }
        
        this.timestampToDateString = function(timestamp) {
            t = timestamp.split(/[- :]/);
            dateobject = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
            options = {
                year: "numeric",
                month: "numeric",
                day: "numeric"
            }
            datestring = dateobject.toLocaleDateString("en-US", options)
            return datestring
        }
        this.date = this.timestampToDateString(timestamp)
    }

    function ItemOrdered(name, quantity, price) {
        this.name = name
        this.quantity = quantity
        this.price = price
        this.item_total = this.quantity * this.price
    }

    function writeOrders(orders) {
        headersdivstring = ''
        headersdivstring += '<tr class="orders_headers">'
        headersdivstring += '<th>Order ID</th>'
        headersdivstring += '<th>Order Date</th>'
        headersdivstring += '<th>Total</th>'
        headersdivstring += '<th>Items Ordered</th>'
        headersdivstring += '</tr>'
        $('#orders_content').html(headersdivstring)
        for (i = 0; i < orders.length; i++) {
            rowdivstring = ''
            rowdivstring += '<tr class="orders_row" id="' + orders[i].id + '_orders_row">'
            rowdivstring += '<td>' + orders[i].id + '</td>'
            rowdivstring += '<td>' + orders[i].date + '</td>'
            rowdivstring += '<td>$' + orders[i].total.toFixed(2) + '</td>'
            itemsdivstring = '';
            for(item_id in orders[i].items_ordered) {
                item = orders[i].items_ordered[item_id]
                itemsdivstring += '<div class="ordered_item">' + item.name + ' x ' + item.quantity + ', $' + item.price.toFixed(2) + 'ea</div>'
            }
            rowdivstring += '<td>' + itemsdivstring + '</td>'
            rowdivstring += '</tr>'
            $('#orders_content').append(rowdivstring)
        }
    }

    function continueShopping() {
        window.location.href = 'products.php';
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
<h1>Order History</h1>
<table id="orders_content">
</table>
<div id="buttons">
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