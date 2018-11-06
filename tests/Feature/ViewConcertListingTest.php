<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_a_concert_listing()
    {
        $this->withoutExceptionHandling();

        // arrange
        // create a concert
        $concert = Concert::create([
            'title'                 => 'The Red Chord',
            'subtitle'              => 'with Animosity and Lethargy',
            'date'                  => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price'          => 3250,
            'venue'                 => 'The Mosh Pit',
            'venue_address'         => '123 Example Lane',
            'city'                  => 'Laraville',
            'state'                 => 'ON',
            'zip'                   => '17916',
            'addtional_information' => 'For tickets, call (555) 555-5555.'
        ]);

        // act
        // view the concert listing
        $response = $this->get('/concerts/' . $concert->id);

        // assert
        // see the concert details
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('December 13, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville');
        $response->assertSee('ON');
        $response->assertSee('17916');
        $response->assertSee('For tickets, call (555) 555-5555.');
    }
}
