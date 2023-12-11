<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 1em;
        }

        section {
            margin: 1em;
            padding: 1em;
            background-color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>

    <header>
        <h1>Order Details</h1>
    </header>

    <section>
        <h2>User Details</h2>
        <p><strong>User Name:</strong> {{$order['user']['name']}}</p>
        <p><strong>Email:</strong> {{$order->email}}</p>
        <p><strong>Address:</strong> {{$order->address}}</p>
        <!-- Add more user details as needed -->
    </section>

    <section>
        <h2>Product Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->products_order as $value)
                    <tr>
                        <td>{{ $value['product']['name'] }}</td>
                        <td>{{ $value['qty_product'] }}</td>
                        <td>${{ $value['price'] }}</td>
                    </tr>
                @endforeach
                <!-- Add more rows for additional products -->
            </tbody>
        </table>
    </section>

    <section>
        <h2>Order Summary</h2>
        <p><strong>Total types of products on order:</strong> {{$order->qty}}</p>
        <p><strong>Total Price:</strong> ${{$order->total_price}}</p>
        <p><strong>Status:</strong> {{$order->status}}</p>
        <!-- Add more order details as needed -->
    </section>

</body>
</html>