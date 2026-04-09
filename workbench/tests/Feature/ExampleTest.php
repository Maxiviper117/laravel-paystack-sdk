<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertRedirect('/paystack/demo');
});
