<?php session_start();?>
<!doctype html>
<html>
<head>
<link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">"> 
    <meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">
<title>The Floral Arrangement</title>
<style type="text/css">
    h1{margin-top: 75px; text-align: center;}
    #cart_content{width: 950px; margin: 25px auto; text-align: center; font-size: 22px; border-collapse: collapse;}
    .remove_button{font-size: 18px;}
    .cart_item{border-bottom: 1px solid black;}
    .cart_item td{padding: 15px 0px;}
    #total{border-bottom:none;}
    #total{font-weight: bold;}
    .cart_headers{border-bottom: 2px solid black;}
    .cart_headers th{ padding-bottom: 15px;}
    #buttons{text-align: center;}
    #buttons input{font-size: 25px; margin: 0 50px;}
    .background{background-image: url(images/background.webp); background-size:cover; object-fit: cover; padding-top: 15px; height: 95%;}
    body{height: 100%;}
    html{height: 100%;}

    @media screen and (max-width: 1050px) {
        h1{margin-top: 120px;}
    }

    @media screen and (max-width: 1000px) {
        #cart_content{width: 95%; min-width: 500px;}
    }

    @media screen and (max-width: 700px) {
        h1{font-size: 35px;}
        #cart_content{font-size: 18px;}
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
        products = {}
        <?php 
            $session_data = json_encode($_SESSION);
            echo "session_data = $session_data;";
            $configs = include('config.php');
            $server = $configs['server'];
            $userid = $configs['userid'];
            $pw = $configs['pw'];
            $db = $configs['db'];

            $conn = new mysqli($server, $userid, $pw);

            $conn->select_db($db);
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $name = $row['name'];
                    $price = $row['price'];
                    $description = $row['description'];
                    $imageurl = $row['imageurl'];
                    echo <<<END
                    product = new Product($id, "$name",$price,"$description", "$imageurl");
                    products[$id] = product;
                    END;
                }
            } else {
                echo "no results";
            }

            $conn->close();
        ?>

        cart_qtys = {}
        for (key in session_data) {
            if (key.startsWith('qty_')) {
                id = key.slice(4)
                cart_qtys[id] = session_data[key]
            }
        }
        cart_total = writeCart(cart_qtys, products)
    })

    function Product(id, name, price, description, imageurl) {
        this.id = id
        this.name = name
        this.price = price
        this.description = description
        this.imageurl = imageurl
        this.getDivString = function() {
            divstring = ''
            divstring += '<div class="product">'
            divstring += '<img src="' + this.imageurl + '">'
            divstring += '<div class="product_info">'
            divstring += '<div class="product_name">' + this.name + '</div>'
            divstring += '<div class="price">$' + this.price.toFixed(2) + '</div>'
            divstring += '<form name="' + this.id + '_qty_select" class="qty_select_form">'
            divstring += '<select name="qty_select" id="' + this.id + '_select" class="qty_select">'
            for (i = 1; i <= 10; i++) {
                divstring += '<option value="' + i + '">' + i + '</option>'
            }
            divstring += '</select></form>'
            divstring += '<div class="buttons">'
            divstring += '<input type="button" value = "Add to Cart" id="' + this.id + '_addtocart" class="add_button" onclick=\'addToCart(' + this.id + ')\'>'
            divstring += '<input type="button" value = "More" id="' + this.id + '_more" class="more_button" onclick=\'toggleMoreText("#' + this.id + '_more_text")\'>'
            divstring += '</div></div>'
            divstring += '<div class="more_text" id="' + this.id + '_more_text">' + this.description + '</div>'
            divstring += '</div>'
            return divstring
        }

        this.addToListings = function() {
            divstring = this.getDivString()
            $('#listings').append(divstring)
        }
    }

    function writeCart(cart_qtys, products) {
        headersdivstring = ''
        headersdivstring += '<tr class="cart_headers">'
        headersdivstring += '<th>Name</th>'
        headersdivstring += '<th>Price</th>'
        headersdivstring += '<th>Quantity</th>'
        headersdivstring += '<th>Total</th>'
        headersdivstring += '<th>&nbsp;</th>'
        headersdivstring += '</tr>'
        $('#cart_content').html(headersdivstring)
        cart_total = 0
        for (id in cart_qtys) {
            if(cart_qtys[id] == 0) {
                continue
            }
            product_total = products[id].price*cart_qtys[id]
            cart_total += product_total
            rowdivstring = ''
            rowdivstring += '<tr class="cart_item" id="' + id + '_cart_item">'
            rowdivstring += '<td>' + products[id].name + '</td>'
            rowdivstring += '<td>$' + products[id].price.toFixed(2) + '</td>'
            rowdivstring += '<td>' + cart_qtys[id] + '</td>'
            rowdivstring += '<td>$' + product_total.toFixed(2) + '</td>'
            rowdivstring += '<td><input type="button" value = "Remove From Cart" id="' + id + '_removefromcart" class="remove_button" onclick="removeFromCart(' + id + ')"></td>'
            rowdivstring += '</tr>'
            $('#cart_content').append(rowdivstring)
        }
        totaldivstring = ''
        totaldivstring += '<tr class="cart_item" id="total">'
        totaldivstring += '<td>&nbsp;</td>'
        totaldivstring += '<td>&nbsp;</td>'
        totaldivstring += '<td>Total:</td>'
        totaldivstring += '<td>$' + cart_total.toFixed(2) + '</td>'
        totaldivstring += '<td>&nbsp;</td>'
        totaldivstring += '</tr>'
        $('#cart_content').append(totaldivstring)
        return cart_total
    }

    function continueShopping() {
        window.location.href = 'products.php';
    }

    function checkOut() {
        if (cart_total == 0) {
            alert("Your cart is empty!")
        } else {
            window.location.href = 'thankyou.php?cart_total=' + cart_total;
        }
    }

    function removeFromCart(id) {
        setSessionVar('qty_'+id, 0)
        cart_qtys[id] = 0
        writeCart(cart_qtys, products)
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
<h1>Cart</h1>
<table id="cart_content">
</table>
<div id="buttons">
<input type="button" value = "Check Out" id="checkout" onclick="checkOut()">
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