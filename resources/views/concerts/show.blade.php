<h1>{{ $concert->title }}</h1>
<h1>{{ $concert->subtitle }}</h1>
<p>{{ $concert->date }}</p>
<p>{{ $concert->date->format('F j, Y') }}</p>
<p>Doors at {{ $concert->date->format('g:ia') }}</p>
<p>{{ number_format($concert->ticket_price / 100, 2) }}</p>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>{{ $concert->city }}, {{ $concert->state }}, {{ $concert->zip }}</p>
<p>{{ $concert->addtional_information }}</p>
