<?php

it('responds to the start route', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('meetups'));
});
