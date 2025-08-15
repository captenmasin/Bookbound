<?php

use Laravel\Dusk\Browser;

test('user can see homepage', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')->assertSee('Your Reading Life at a Glance')
            ->assertSee('How it works');
    });
});
