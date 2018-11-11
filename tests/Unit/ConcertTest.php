<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date()
    {
        // create a concert with a known date
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        // retrieve the formatted date
        $date = $concert->formatted_date;

        // verify the date is formatted as expected
        $this->assertEquals('December 1, 2016', $date);
    }
}
