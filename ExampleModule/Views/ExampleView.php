<?php
namespace MonumX\Views;

use MonumX\Exceptions\BadRequestException;

class ExampleView {
    // GET request
    public function get($request, $response) {
        // Get a URL param
        $urlParam = (string) $request->urlParam('someParam');

        // Is the param valid?
        if (!$urlParam !== 'testval') {
            throw new BadRequestException('Invalid param provided');
        }

        // Add a response
        $response->serialize(array('test' => $urlParam));
    }
}
?>
