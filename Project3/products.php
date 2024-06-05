<?php session_start();?>
<!doctype html>
<html>
<head>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">"> 
    <meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">
<title>The Floral Arrangement</title>
<style type="text/css">
    h1{margin-top: 75px; text-align: center;}
    #listings {display: grid; grid-template-columns: auto auto auto; gap: 10px; width: fit-content; margin: 25px auto;  margin-bottom: 100px;}
    .product{border: 2px solid #000; height: 100%; width: 500px; background-color: white;}
    .product img {margin: 20px; margin-bottom: 10px; height: 200px; width: 200px; border: 1px solid #000; display: inline-block;}
    .product_info{display: inline-block; vertical-align: top; margin-top: 25px; max-width: 350px;}
    .product_name{font-size: 30px; font-weight: bold; width: fit-content;}
    .price {font-size: 22px; width: fit-content;}
    .qty_select {font-size: 16px; width: 60px; text-align: center;}
    .buttons{width: fit-content;}
    .price,.qty_select,.buttons{margin-top: 22px;}
    .add_button,.more_button{font-size: 18px;}
    .more_button{margin-left: 20px;}
    .qty_select_form{width: fit-content;}
    .more_text{margin-bottom: 15px; padding: 0px 20px; box-sizing: border-box; display: none; font-size: 20px;}
    .incart_text{display: inline; margin-left: 5px; font-size: 19px;}
    .background{background-image: url(images/background.webp); background-size:cover; object-fit: cover; padding-top: 15px; height: 95%;}
    body{height: 100%;}
    html{height: 100%;}

    @media screen and (max-width: 1600px) {
        #listings{grid-template-columns: auto auto;}
        html{height: 1650px;}
    }

    @media screen and (max-width: 1050px) {
        #listings{grid-template-columns: auto;}
        h1{margin-top: 50px;}
        html{height: 2650px;}
        .background{padding-top: 75px;}
    }

    @media screen and (max-width: 600px) {
        h1{margin-top: 150px;}
        .background{padding-top: 75px;} 
        html{height: 2800px;}
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
        // for (id in products) {
        //     products[id].addToListings()
        // }

        cart_qtys = {}
        for (key in session_data) {
            if (key.startsWith('qty_')) {
                id = key.slice(4)
                cart_qtys[id] = session_data[key]
            }
        }

        showListings()
    })

    function Product(id, name, price, description, imageurl, qty_incart) {
        this.id = id
        this.name = name
        this.price = price
        this.description = description
        this.imageurl = imageurl
        this.getDivString = function() {
            cart_qty = 0
            if (this.id in cart_qtys) {
                cart_qty = cart_qtys[this.id]
            }
            divstring = ''
            divstring += '<div class="product">'
            divstring += '<img src="' + this.imageurl + '">'
            divstring += '<div class="product_info">'
            divstring += '<div class="product_name">' + this.name + '</div>'
            divstring += '<div class="price">$' + this.price.toFixed(2) + '</div>'
            divstring += '<div class="qty_line>'
            divstring += '<form name="' + this.id + '_qty_select" class="qty_select_form">'
            divstring += '<select name="qty_select" id="' + this.id + '_select" class="qty_select">'
            for (i = 1; i <= 10; i++) {
                divstring += '<option value="' + i + '">' + i + '</option>'
            }
            divstring += '</select></form>'
            divstring += '<div class="incart_text" id = "' + this.id + '_incart_text"> In Cart: ' + cart_qty + '</div>'
            divstring += '</div>'
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
            // $('.incart_text').hide();
        }
    }

    function toggleMoreText(id_more_text) {
        $(id_more_text).toggle();
    }

    async function addToCart(id) {
        select_id = '#' + id + '_select'
        
        prev_qty = 0
        if (id in cart_qtys) {
            prev_qty = cart_qtys[id]
        }
        addl_qty = $(select_id).val();
        new_qty = parseInt(prev_qty) + parseInt(addl_qty)
        await setSessionVar('qty_'+id, new_qty)
        // if (success) {
        //     console.log("Success")
        window.location.href = 'cart.php';
        // }
        // cart_qtys[id] = qty;
        // $('#listings').html('')
        // showListings()
    }

    function showListings() {
        for (id in products) {
            products[id].addToListings()
            if ((!(id in cart_qtys)) || (cart_qtys[id] == 0)) {
                incart_text_id = '#' + id + '_incart_text'
                $(incart_text_id).hide();
            }
        }
    }

    // function removeFromCart(id) {
    //     setSessionVar('qty_'+id, 0)
    // }

    async function setSessionVar(key, value) {
        request = new XMLHttpRequest();
        request.open("POST", "setsessionvar.php", false);
        request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        request.onreadystatechange = function() {
            if (request.readyState == 4 && request.status == 200) {
                console.log(request.responseText)
                }
            else if (request.readyState == 4 && request.status != 200) {
                console.log(request.readyState)
                console.log(request.status)
                console.log('Request Failed!')
                // return false
            }
        }
        data = key + '=' + value
        request.send(data);
        // return true
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
<h1>Our Products</h1>
<div id="listings">
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
</div>
</body>