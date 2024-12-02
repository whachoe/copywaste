<?php
define('WASTE_ROOT_DIR', __DIR__ .'/../wastes');
define('QRCODE_DIR', __DIR__.'/qrcodes');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Waste.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use CopyWaste\Waste;
use Monolog\Logger;

// Setup PHP Template engine
$renderer = new PhpRenderer(__DIR__ . '/../templates');

// Setup Monolog
$logger = new Logger('copywaste');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/dev/stderr', Logger::DEBUG));

// Create App
$app = AppFactory::create();

// Add Middleware
$app->addBodyParsingMiddleware();

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Routes
$app->get('/', function (Request $request, Response $response, $args) {
    // Make a Waste with a random ID and redirect to it
    $waste = new Waste();
    $waste->save();
    $id = $waste->getId();

    // Redirect to the new waste
    return $response->withHeader('Location', "/waste/$id")->withStatus(302);
  });

$app->get('/waste/{id}', function (Request $request, Response $response, $args) use ($renderer) {
    $id = $args['id'];
    $waste = Waste::load($id);
    if ($waste == null) {
      $response->getBody()->write("Waste not found");
      return $response->withStatus(404);
    } else {
        $viewData = [
            'id' => $id,
            'message' => $waste->getMessage(),
            'uploads' => $waste->getUploads(),
        ];

        return $renderer->render($response, 'index.php', $viewData);
    }

    return $response->withStatus(500);
});

$app->get('/api/v2/waste/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $waste = Waste::load($id);

    if ($waste == null) {
      $response->getBody()->write("Waste not found");
      return $response->withStatus(404);
    } else {
        $response->getBody()->write(htmlspecialchars($waste->getMessage(), 0, null, false));
    }

    return $response;
});

$app->put('/api/v2/waste/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $waste = Waste::load($id);
    $parsedBody = $request->getParsedBody();

    if ($waste == null) {
      $response->getBody()->write("Waste not found");
      return $response->withStatus(404);
    } else {
        $waste->setMessage(htmlspecialchars($parsedBody['message'], 0, null, false));
        $waste->save();
    }

    $response->getBody()->write($waste->getMessage());
    return $response;
});

$app->post('/api/v2/upload/{id}', function (Request $request, Response $response, $args) use ($renderer, $logger) {
    $id = $args['id'];
    $waste = Waste::load($id);

    if ($waste == null) {
        $response->getBody()->write("Waste not found");
        return $response->withStatus(404);
    } else {
        // handle single input with single file upload
        $files = $request->getUploadedFiles();
        $uploadedFile = $files['uploadfile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $waste->addUpload($uploadedFile);
            $viewData = [
                'id' => $id,
                'uploads' => $waste->getUploads(true),
            ];

            return $renderer->render($response, 'uploadpanel.php', $viewData);
        } else {
            $logger->error("Upload error: ".$uploadedFile->getError());
        }
    }

    return $response->withStatus(500);
});

$app->get('/api/v2/download/{id}/{filename}', function (Request $request, Response $response, $args)  {
    $id = $args['id'];
    $filename = base64_decode($args['filename']);
    $filepath = WASTE_ROOT_DIR . "/$id/uploads/$filename";

    if (file_exists($filepath)) {
        $content_type = mime_content_type($filepath) ? mime_content_type($filepath) : 'application/octet-stream';
        $response = $response->
            header('Content-Description: File Transfer');
            header("Content-Type: $content_type");
            header('Content-Disposition: attachment; filename=' . basename($filepath));
//            header('Content-Transfer-Encoding: binary')
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '. filesize($filepath));
            readfile($filepath);
            exit();
    } else {
        $response->getBody()->write("File not found");
        return $response->withStatus(404);
    }

    return $response->withStatus(500);
});

$app->delete('/api/v2/upload/{id}/{filename}', function (Request $request, Response $response, $args) use ($renderer) {
    $id = $args['id'];
    $filename = base64_decode($args['filename']);
    $filepath = WASTE_ROOT_DIR . "/$id/uploads/$filename";

    $id = $args['id'];
    $waste = Waste::load($id);

    if ($waste == null) {
        $response->getBody()->write("Waste not found");
        return $response->withStatus(404);
    } else {
        if (file_exists($filepath)) {
            $uploads = $waste->deleteUpload($filename);
        }

        $viewData = [
            'id' => $id,
            'uploads' => $uploads,
        ];

        return $renderer->render($response, 'uploadpanel.php', $viewData);
    }

    return $response->withStatus(500);
});

// Main loop
$app->run();