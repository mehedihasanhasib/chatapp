<?php
$host = '127.0.0.1';
$port = 2812;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, $host, $port);
socket_listen($socket);

$clients = [];

echo "WebSocket Server is listening on ws://$host:$port\n";

while (true) {
    $read = [$socket];
    $read = array_merge($read, $clients);

    socket_select($read, $write, $except, 0, 10);

    // Handle new client connections
    if (in_array($socket, $read)) {

        $clientSocket = socket_accept($socket);
        $clients[] = $clientSocket;

        $request = socket_read($clientSocket, 5000);
        perform_handshake($clientSocket, $request);

        unset($read[array_search($socket, $read)]);
    }

    // Handle incoming messages from connected clients
    foreach ($read as $clientSocket) {
        $data = socket_read($clientSocket, 1024, PHP_BINARY_READ);

        if ($data === false) {
            unset($clients[array_search($clientSocket, $clients)]);
            socket_close($clientSocket);
            continue;
        }

        // Decode the WebSocket message
        $decodedMessage = decode_message($data);

        // Check if it's a user data message
        if (strpos($decodedMessage, 'userData') !== false) {
            $userData = json_decode($decodedMessage, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($userData['userData']['userId'])) {
                $userId = $userData['userData']['userId'];
                if (is_string($userId) || is_int($userId)) {
                    $userConnections[$userId] = $clientSocket;
                    send_message($clientSocket, json_encode(['type' => 'userDataResponse', 'message' => 'User data received']));
                } else {
                    error_log("Invalid userId type: " . gettype($userId));
                }
            } else {
                error_log("Invalid userData JSON: " . $decodedMessage);
            }
        } else {
            // Decode JSON into PHP array
            $receivedData = json_decode($decodedMessage, true);
            if ($receivedData !== null) {
                $reveiver_id = (int)$receivedData['receiver_id'];
                // Send back a response (array converted to JSON)
                $jsonResponse = json_encode($receivedData);

                foreach ($clients as $client) {
                    foreach ($userConnections as $key => $user) {
                        if ($reveiver_id == $key) {
                            if ($user === $client) {
                                send_message($client, $jsonResponse);
                            }
                        }
                    }
                }
            }
        }
    }
}

function perform_handshake($clientSocket, $request)
{
    $headers = [];
    $lines = preg_split("/\r\n/", $request);
    foreach ($lines as $line) {
        if (strpos($line, ": ") !== false) {
            list($key, $value) = explode(": ", $line);
            $headers[$key] = $value;
        }
    }

    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack(
        'H*',
        sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
    ));

    $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: $secAccept\r\n\r\n";

    socket_write($clientSocket, $upgrade, strlen($upgrade));
}

function decode_message($data)
{
    $unmaskedPayload = '';
    $length = ord($data) & 127;

    if ($length == 126) {
        $masks = substr($data, 4, 4);
        $payload = substr($data, 8);
    } elseif ($length == 127) {
        $masks = substr($data, 10, 4);
        $payload = substr($data, 14);
    } else {
        $masks = substr($data, 2, 4);
        $payload = substr($data, 6);
    }

    for ($i = 0; $i < strlen($payload); ++$i) {
        $unmaskedPayload .= $payload[$i] ^ $masks[$i % 4];
    }

    return $unmaskedPayload;
}

function send_message($clientSocket, $message)
{
    $header = "81"; // 10000001 indicates final frame and text frame
    $length = strlen($message);

    if ($length <= 125) {
        $header .= sprintf('%02X', $length);
    } elseif ($length > 125 && $length < 65536) {
        $header .= "7E" . sprintf('%04X', $length);
    } else {
        $header .= "7F" . sprintf('%016X', $length);
    }

    $encodedMessage = hex2bin($header) . $message;
    // print_r($encodedMessage);
    socket_write($clientSocket, $encodedMessage, strlen($encodedMessage));
}
