<?php
$host = '127.0.0.1';
$port = 2801;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, $host, $port);
socket_listen($socket);
// socket_close($socket);
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
        $data = socket_read($clientSocket, 25600, PHP_BINARY_READ);

        if ($data === false) {
            unset($clients[array_search($clientSocket, $clients)]);
            socket_close($clientSocket);
            continue;
        } else {
            $decodedMessage = decode_message($data);
        }

        // Decode the WebSocket message

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
                $receivedData["receiver_id"] = (int)$receivedData['receiver_id'];
                // Send back a response (array converted to JSON)
                $jsonResponse = json_encode($receivedData);

                foreach ($clients as $client) {
                    foreach ($userConnections as $key => $user) {
                        if ($receivedData["receiver_id"] == $key) {
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
    if (empty($data)) {
        return null; // or return an error message, depending on your needs
    }
    $unmaskedPayload = '';
    
    $firstByte = ord($data[0]) ?? null;

    $isFinalFragment = ($firstByte & 0b10000000) !== 0;  // FIN bit (first bit of the first byte)
    $opcode = $firstByte & 0b00001111;  // Opcode (4 bits: text, binary, close, etc.)

    if ($opcode !== 1) {
        // error_log("Non-text frame received. Opcode: " . $opcode);
        return null;
    }

    $secondByte = ord($data[1]);
    $masked = ($secondByte & 0b10000000) !== 0;  // Mask bit (indicates whether the payload is masked)
    $length = $secondByte & 0b01111111;  // Payload length (7 bits)

    $offset = 2;
    if ($length === 126) {
        $length = unpack('n', substr($data, 2, 2))[1];  // 126 means the length is in the next 2 bytes
        $offset += 2;
    } elseif ($length === 127) {
        $length = unpack('J', substr($data, 2, 8))[1];  // 127 means the length is in the next 8 bytes
        $offset += 8;
    }

    $masks = '';
    if ($masked) {
        $masks = substr($data, $offset, 4);
        $offset += 4;
    }

    $payload = substr($data, $offset);

    // Unmask the payload if needed
    for ($i = 0; $i < strlen($payload); ++$i) {
        $unmaskedPayload .= $masked ? $payload[$i] ^ $masks[$i % 4] : $payload[$i];
    }

    // If this is a fragmented message, we need to accumulate the fragments
    if (!$isFinalFragment) {
        // Store the fragment and wait for more
        global $messageFragments;
        $messageFragments[] = $unmaskedPayload;
        return null;
    }

    // If it's the final fragment, reassemble the message
    global $messageFragments;
    if (!empty($messageFragments)) {
        $unmaskedPayload = implode('', $messageFragments) . $unmaskedPayload;
        $messageFragments = [];  // Clear the fragment buffer
    }

    // error_log("Decoded message: " . $unmaskedPayload);
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
