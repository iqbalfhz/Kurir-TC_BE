<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    // Application root redirects to login in this project; accept 302
    $response->assertStatus(302);
});
