<meta name="viewport" content="width=device-width, initial-scale=1">

@if($editor)
    <!-- Hidden CSSes -->
@else
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
@endif

<div class="container-fluid">
    <div class="col-md-12">
        <h1>{{ $title }}</h1>
        <img src="{{ $image }}" class="rounded mx-auto d-block">
        <h3><span class="glyphicon glyphicon-list-alt"></span> Description</h3>
        <p>{{ $description }}</p>
        <h3>Features:</h3>
        <ul>
            @foreach($features as $feature)
                <li>{{ $feature }}</li>
            @endforeach
        </ul>

        <ul class="list-unstyled">
            <li>
                <h3><span class="glyphicon glyphicon-time"></span> Handling</h3>
                <p>We will ship all orders within
                    <mark>3 business day</mark>
                    of payment.
                </p>
            </li>

            <li>
                <h3><span class="glyphicon glyphicon-send"></span> Delivery</h3>
                We Do Not Ship Outside of the Continental US.
            </li>

            <li>
                <h3><span class="glyphicon glyphicon-ok-circle"></span> Return Policy</h3>
                All items qualify for returns within 30 days of receipt. Buyer is responsible for return shipping on any
                item that is not damaged.
            </li>

            <li>
                <h3><span class="glyphicon glyphicon-comment"></span> Feedback</h3>
                <p>We take our reputation seriously, we buy and sell online, so we understand the value of trust.
                    <mark>If you are unsatisfied with your order, please contact us</mark>
                    and we will work with you to resolve it to your satisfaction.
                </p>
            </li>
        </ul>
    </div>
</div>